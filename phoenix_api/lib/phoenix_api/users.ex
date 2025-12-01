defmodule PhoenixApi.Users do
  @moduledoc """
  The Users context with full filtering, sorting and pagination.
  """

  import Ecto.Query, warn: false
  alias PhoenixApi.Repo
  alias PhoenixApi.Users.User

  # ======================================
  # PUBLIC API
  # ======================================

  # GET /users → lista z filter/sort/page
  def list_users(params \\ %{}) do
    User
    |> filter_first_name(params)
    |> filter_last_name(params)
    |> filter_gender(params)
    |> filter_birth_from(params)
    |> filter_birth_to(params)
    |> apply_sort(params)
    |> paginate(params)
    |> Repo.all()
  end

  # Łącza do paginacji
  def count_users(params \\ %{}) do
    User
    |> filter_first_name(params)
    |> filter_last_name(params)
    |> filter_gender(params)
    |> filter_birth_from(params)
    |> filter_birth_to(params)
    |> Repo.aggregate(:count, :id)
  end

  # ======================================
  # FILTERS
  # ======================================

  defp filter_first_name(query, %{"first_name" => value}) when value != "" do
    from u in query, where: ilike(u.first_name, ^"%#{value}%")
  end
  defp filter_first_name(query, _), do: query

  defp filter_last_name(query, %{"last_name" => value}) when value != "" do
    from u in query, where: ilike(u.last_name, ^"%#{value}%")
  end
  defp filter_last_name(query, _), do: query

  defp filter_gender(query, %{"gender" => gender}) when gender != "" do
    from u in query, where: u.gender == ^gender
  end
  defp filter_gender(query, _), do: query

  defp filter_birth_from(query, %{"birthdate_from" => from}) when from != "" do
    {:ok, date} = Date.from_iso8601(from)
    from u in query, where: u.birthdate >= ^date
  end
  defp filter_birth_from(query, _), do: query

  defp filter_birth_to(query, %{"birthdate_to" => to}) when to != "" do
    {:ok, date} = Date.from_iso8601(to)
    from u in query, where: u.birthdate <= ^date
  end
  defp filter_birth_to(query, _), do: query

  # ======================================
  # SORTING
  # ======================================

  @sortable_fields %{
    "first_name" => :first_name,
    "last_name"  => :last_name,
    "gender"     => :gender,
    "birthdate"  => :birthdate
  }

  defp apply_sort(query, %{"sort_by" => field, "sort_order" => order})
       when order in ["asc", "desc"] do

    case Map.get(@sortable_fields, field) do
      nil ->
        query

      atom_field ->
        direction = String.to_atom(order)
        from u in query, order_by: [{^direction, field(u, ^atom_field)}]
    end
  end

  defp apply_sort(query, _), do: query

  # ======================================
  # PAGINATION
  # ======================================

  defp paginate(query, %{"page" => page, "page_size" => size})
       when page != "" and size != "" do

    page = String.to_integer(page)
    size = String.to_integer(size)

    offset = (page - 1) * size

    query
    |> limit(^size)
    |> offset(^offset)
  end

  defp paginate(query, _), do: query

  # ======================================
  # CRUD
  # ======================================

  def get_user!(id), do: Repo.get!(User, id)

  def create_user(attrs) do
    %User{}
    |> User.changeset(attrs)
    |> Repo.insert()
  end

  def update_user(%User{} = user, attrs) do
    user
    |> User.changeset(attrs)
    |> Repo.update()
  end

  def delete_user(%User{} = user), do: Repo.delete(user)

  def change_user(%User{} = user, attrs \\ %{}) do
    User.changeset(user, attrs)
  end
end
