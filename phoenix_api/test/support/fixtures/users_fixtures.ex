defmodule PhoenixApi.UsersFixtures do
  @moduledoc """
  This module defines test helpers for creating
  entities via the `PhoenixApi.Users` context.
  """

  @doc """
  Generate a user.
  """
  def user_fixture(attrs \\ %{}) do
    {:ok, user} =
      attrs
      |> Enum.into(%{
        birthdate: ~D[2025-11-29],
        first_name: "some first_name",
        gender: "some gender",
        last_name: "some last_name"
      })
      |> PhoenixApi.Users.create_user()

    user
  end
end
