defmodule PhoenixApi.CsvImporter do
  @moduledoc """
  CSV importer for PESEL-based name and surname datasets.

  Loads CSV files from priv/data, validates them,
  selects the top 100 most popular entries per dataset,
  and generates random users with correct gender and dates.
  """

  alias PhoenixApi.Users
  alias PhoenixApi.Users.User
  alias PhoenixApi.Repo

  NimbleCSV.define(CSVParser, separator: ",", escape: "\"")

  @data_path Application.app_dir(:phoenix_api, "priv/data")

  # ============================================================
  # PUBLIC FUNCTION – generates N users from PESEL CSV datasets
  # ============================================================
  def generate_users(count \\ 100) do
    with :ok <- validate_csv("names_female.csv"),
         :ok <- validate_csv("names_male.csv"),
         :ok <- validate_csv("surname_female.csv"),
         :ok <- validate_csv("surname_male.csv") do

      female_names = load_top_names("names_female.csv")
      male_names   = load_top_names("names_male.csv")
      surnames_f   = load_top_surnames("surname_female.csv")
      surnames_m   = load_top_surnames("surname_male.csv")

      for _ <- 1..count do
        {first, last, gender} =
          case Enum.random([:female, :male]) do
            :female ->
              {Enum.random(female_names), Enum.random(surnames_f), "female"}

            :male ->
              {Enum.random(male_names), Enum.random(surnames_m), "male"}
          end

        {:ok, user} =
          Users.create_user(%{
            "first_name" => first,
            "last_name" => last,
            "gender" => gender,
            "birthdate" => random_birthdate()
          })

        user
      end
    else
      {:error, msg} ->
        raise "CSV Import failed: #{msg}"
    end
  end

  # ============================================================
  # VALIDATION – ensures CSV files are correct
  # ============================================================
  defp validate_csv(file) do
    path = path(file)
    cond do
      not File.exists?(path) ->
        {:error, "CSV file #{file} does not exist"}

      true ->
        sample =
          path
          |> File.stream!()
          |> CSVParser.parse_stream()
          |> Enum.take(5)

        cond do
          sample == [] ->
            {:error, "CSV file #{file} is empty"}

          # names files → 3 columns
          file in ["names_female.csv", "names_male.csv"] and
            Enum.any?(sample, fn row -> length(row) != 3 end) ->
              {:error, "CSV file #{file} has invalid rows (expected 3 columns)"}

          # surname files → 2 columns
          file in ["surname_female.csv", "surname_male.csv"] and
            Enum.any?(sample, fn row -> length(row) != 2 end) ->
              {:error, "CSV file #{file} has invalid rows (expected 2 columns)"}

          true ->
            :ok
        end
    end
  end

  # ============================================================
  # LOADERS – extract TOP 100 names & surnames
  # ============================================================
  defp load_top_names(file) do
    file
    |> path()
    |> File.stream!()
    |> CSVParser.parse_stream()
    |> Stream.drop(1) # skip header
    |> Enum.map(fn
      [first_name, _gender, count] ->
        {String.capitalize(String.downcase(first_name)), String.to_integer(count)}

      row ->
        raise "CSV row invalid in #{file}: #{inspect(row)}"
    end)
    |> Enum.sort_by(fn {_name, count} -> -count end)
    |> Enum.take(100)
    |> Enum.map(&elem(&1, 0))
  end
  defp load_top_surnames(file) do
    file
    |> path()
    |> File.stream!()
    |> CSVParser.parse_stream()
    |> Enum.map(fn [surname, count] ->
      clean = surname |> String.downcase() |> String.capitalize()
      {clean, String.to_integer(count)}
    end)
    |> Enum.sort_by(fn {_name, count} -> -count end)
    |> Enum.take(100)
    |> Enum.map(&elem(&1, 0))
  end

  defp path(file), do: Path.join(@data_path, file)

  # ============================================================
  # RANDOM BIRTHDATE – range 1970–2024
  # ============================================================
  defp random_birthdate do
    year = Enum.random(1970..2024)
    month = Enum.random(1..12)
    day = Enum.random(1..28)
    Date.new!(year, month, day)
  end
end
