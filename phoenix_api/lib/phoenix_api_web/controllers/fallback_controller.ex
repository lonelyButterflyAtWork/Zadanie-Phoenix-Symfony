defmodule PhoenixApiWeb.FallbackController do
  use PhoenixApiWeb, :controller

  # Handles Ecto changeset errors
  def call(conn, {:error, %Ecto.Changeset{} = changeset}) do
    conn
    |> put_status(:unprocessable_entity)
    |> put_view(PhoenixApiWeb.ErrorJSON)
    |> render("422.json", changeset: changeset)
  end

  # Handles not found errors
  def call(conn, {:error, :not_found}) do
    conn
    |> put_status(:not_found)
    |> put_view(PhoenixApiWeb.ErrorJSON)
    |> render("404.json")
  end

  # Handles generic errors
  def call(conn, {:error, reason}) do
    conn
    |> put_status(:bad_request)
    |> put_view(PhoenixApiWeb.ErrorJSON)
    |> render("400.json", reason: reason)
  end
end
