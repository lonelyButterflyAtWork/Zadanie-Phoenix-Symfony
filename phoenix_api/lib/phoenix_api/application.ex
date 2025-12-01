defmodule PhoenixApi.Application do
  @moduledoc false
  use Application

  @impl true
  def start(_type, _args) do
    children = [
      PhoenixApiWeb.Telemetry,
      PhoenixApi.Repo,
      {DNSCluster, query: Application.get_env(:phoenix_api, :dns_cluster_query) || :ignore},
      {Phoenix.PubSub, name: PhoenixApi.PubSub},
      PhoenixApiWeb.Endpoint
    ]

    opts = [strategy: :one_for_one, name: PhoenixApi.Supervisor]
    {:ok, pid} = Supervisor.start_link(children, opts)

    # First: RUN MIGRATIONS
    run_migrations()

    # Second: AUTOIMPORT FROM CSV
    run_auto_import()

    {:ok, pid}
  end

  # ============================================================
  # 1) AUTO-MIGRATIONS (fixes your error!)
  # ============================================================
  defp run_migrations do
    Task.start(fn ->
      :timer.sleep(500) # wait for Repo to be ready

      IO.puts(">>> Running database migrations…")

      try do
        Ecto.Migrator.run(PhoenixApi.Repo, Application.app_dir(:phoenix_api, "priv/repo/migrations"), :up, all: true)
        IO.puts(">>> Migrations complete.")
      rescue
        e ->
          IO.puts(">>> MIGRATION ERROR: #{inspect(e)}")
      end
    end)
  end

  # ============================================================
  # 2) AUTO-IMPORT USERS FROM CSV (runs AFTER migrations)
  # ============================================================
  defp run_auto_import do
    Task.start(fn ->
      :timer.sleep(2000) # ensure migrations finish first

      case PhoenixApi.Repo.aggregate(PhoenixApi.Users.User, :count, :id) do
        0 ->
          IO.puts(">>> USERS TABLE EMPTY – importing 100 users from CSV…")

          try do
            PhoenixApi.CsvImporter.generate_users(100)
            IO.puts(">>> CSV IMPORT FINISHED.")
          rescue
            e ->
              IO.puts(">>> CSV IMPORT FAILED: #{inspect(e)}")
          end

        _ ->
          IO.puts(">>> Users table already has data – skipping auto import.")
      end
    end)
  end

  @impl true
  def config_change(changed, _new, removed) do
    PhoenixApiWeb.Endpoint.config_change(changed, removed)
    :ok
  end
end
