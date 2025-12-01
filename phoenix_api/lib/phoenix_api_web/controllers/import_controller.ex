defmodule PhoenixApiWeb.ImportController do
  use PhoenixApiWeb, :controller

  def import(conn, _params) do
    users = PhoenixApi.CsvImporter.generate_users(100)

    json(conn, %{imported: length(users)})
  end
end
