defmodule PhoenixApiWeb.Plugs.ImportAuth do
  import Plug.Conn

  def init(opts), do: opts

  def call(conn, _opts) do
    expected = Application.get_env(:phoenix_api, :import_token)
    provided = get_req_header(conn, "x-api-token") |> List.first()

    if provided == expected do
      conn
    else
      conn
      |> send_resp(401, "Unauthorized: Missing or invalid API token")
      |> halt()
    end
  end
end
