defmodule PhoenixApiWeb.ErrorJSON do
  def render("404.json", _assigns) do
    %{error: "not_found", message: "User not found"}
  end

  def render("422.json", %{changeset: changeset}) do
    %{
      error: "unprocessable_entity",
      message: "Validation failed",
      details: Ecto.Changeset.traverse_errors(changeset, fn {msg, opts} ->
        Enum.reduce(opts, msg, fn {key, value}, acc ->
          String.replace(acc, "%{#{key}}", to_string(value))
        end)
      end)
    }
  end

  def render("400.json", _assigns) do
    %{error: "Bad Request", message: "No update attributes provided"}
  end

  def render("500.json", _assigns) do
    %{error: "Internal Server Error", message: "An unexpected error occurred"}
  end

  # fallback for other error templates
  def render(template, _assigns) do
    %{error: Phoenix.Controller.status_message_from_template(template)}
  end
end