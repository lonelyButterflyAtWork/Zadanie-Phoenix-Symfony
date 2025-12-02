defmodule PhoenixApiWeb.Plugs.ImportAuth do
  import Plug.Conn

  def init(opts), do: opts

  def call(conn, _opts) do
    expected = Application.get_env(:phoenix_api, :import_token)
    # Pobierz token z nagłówka X-Import-Key zamiast X-Api-Token
    provided = get_req_header(conn, "x-import-key") |> List.first()

    if provided == expected do
      conn
    else
      conn
      |> send_resp(401, "Unauthorized: Missing or invalid API token")
      |> halt()
    end
  end
end
