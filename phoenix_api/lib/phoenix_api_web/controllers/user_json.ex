defmodule PhoenixApiWeb.UserJSON do
  alias PhoenixApi.Users.User

  def index(%{users: users}) do
    %{data: Enum.map(users, &data/1)}
  end

  def show(%{user: user}) do
    %{data: data(user)}
  end

  defp data(%User{} = user) do
    %{
      id: user.id,
      first_name: user.first_name,
      last_name: user.last_name,
      birthdate: user.birthdate,
      gender: user.gender
    }
  end
end
