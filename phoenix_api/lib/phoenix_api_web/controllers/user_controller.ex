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
    case Users.get_user(id) do
      nil ->
        conn
        |> put_status(:not_found)
        |> put_view(PhoenixApiWeb.ErrorJSON)
        |> render("404.json")

      user ->
        render(conn, :show, user: user)
    end
  end

  def create(conn, params) do
    case Users.create_user(params) do
      {:ok, %User{} = user} ->
        conn
        |> put_status(:created)
        |> render(:show, user: user)

      {:error, changeset} ->
        conn
        |> put_status(:unprocessable_entity)
        |> put_view(PhoenixApiWeb.ErrorJSON)
        |> render("422.json", changeset: changeset)
    end
  end

  def update(conn, %{"id" => id} = params) do
    case Users.get_user(id) do
      nil ->
        conn
        |> put_status(:not_found)
        |> put_view(PhoenixApiWeb.ErrorJSON)
        |> render("404.json")

      user ->
        attrs = Map.delete(params, "id")
        if map_size(attrs) == 0 do
          conn
          |> put_status(:bad_request)
          |> put_view(PhoenixApiWeb.ErrorJSON)
          |> render("400.json")
        else
          case Users.update_user(user, attrs) do
            {:ok, user} ->
              render(conn, :show, user: user)

            {:error, changeset} ->
              conn
              |> put_status(:unprocessable_entity)
              |> put_view(PhoenixApiWeb.ErrorJSON)
              |> render("422.json", changeset: changeset)
          end
        end
    end
  end

  def delete(conn, %{"id" => id}) do
    case Users.get_user(id) do
      nil ->
        conn
        |> put_status(:not_found)
        |> put_view(PhoenixApiWeb.ErrorJSON)
        |> render("404.json")

      user ->
        case Users.delete_user(user) do
          {:ok, _} ->
            send_resp(conn, :no_content, "")

          {:error, changeset} ->
            conn
            |> put_status(:unprocessable_entity)
            |> put_view(PhoenixApiWeb.ErrorJSON)
            |> render("422.json", changeset: changeset)
        end
    end
  end
end
