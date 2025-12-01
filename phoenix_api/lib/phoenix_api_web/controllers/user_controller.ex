defmodule PhoenixApiWeb.UserController do
  use PhoenixApiWeb, :controller

  alias PhoenixApi.Users
  alias PhoenixApi.Users.User

  action_fallback PhoenixApiWeb.FallbackController

  def index(conn, params) do
    users = Users.list_users(params)
    render(conn, :index, users: users)
  end

  def show(conn, %{"id" => id}) do
    user = Users.get_user!(id)
    render(conn, :show, user: user)
  end

  def create(conn, params) do
    with {:ok, %User{} = user} <- Users.create_user(params) do
      conn
      |> put_status(:created)
      |> render(:show, user: user)
    end
  end

  def update(conn, %{"id" => id} = params) do
    user = Users.get_user!(id)

    with {:ok, %User{} = user} <- Users.update_user(user, Map.delete(params, "id")) do
      render(conn, :show, user: user)
    end
  end

  def delete(conn, %{"id" => id}) do
    user = Users.get_user!(id)

    with {:ok, %User{}} <- Users.delete_user(user) do
      send_resp(conn, :no_content, "")
    end
  end
end
