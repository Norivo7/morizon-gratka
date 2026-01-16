defmodule PhoenixApi.Accounts do
  @moduledoc """
  The Accounts context.
  """

  import Ecto.Query, warn: false
  alias PhoenixApi.Repo

  alias PhoenixApi.Accounts.User

  @doc """
  Returns the list of users.

  ## Examples

      iex> list_users()
      [%User{}, ...]

  """
  def list_users(params) when is_map(params) do
    import Ecto.Query, warn: false
    alias PhoenixApi.Repo
    alias PhoenixApi.Accounts.User

    params = normalize_params(params)

    User
    |> apply_filters(params)
    |> apply_sort(params)
    |> Repo.all()
  end

  @doc """
  Gets a single user.

  Raises `Ecto.NoResultsError` if the User does not exist.

  ## Examples

      iex> get_user!(123)
      %User{}

      iex> get_user!(456)
      ** (Ecto.NoResultsError)

  """
  def get_user!(id), do: Repo.get!(User, id)

  @doc """
  Creates a user.

  ## Examples

      iex> create_user(%{field: value})
      {:ok, %User{}}

      iex> create_user(%{field: bad_value})
      {:error, %Ecto.Changeset{}}

  """
  def create_user(attrs) do
    %User{}
    |> User.changeset(attrs)
    |> Repo.insert()
  end

  @doc """
  Updates a user.

  ## Examples

      iex> update_user(user, %{field: new_value})
      {:ok, %User{}}

      iex> update_user(user, %{field: bad_value})
      {:error, %Ecto.Changeset{}}

  """
  def update_user(%User{} = user, attrs) do
    user
    |> User.changeset(attrs)
    |> Repo.update()
  end

  @doc """
  Deletes a user.

  ## Examples

      iex> delete_user(user)
      {:ok, %User{}}

      iex> delete_user(user)
      {:error, %Ecto.Changeset{}}

  """
  def delete_user(%User{} = user) do
    Repo.delete(user)
  end

  @doc """
  Returns an `%Ecto.Changeset{}` for tracking user changes.

  ## Examples

      iex> change_user(user)
      %Ecto.Changeset{data: %User{}}

  """
  def change_user(%User{} = user, attrs \\ %{}) do
    User.changeset(user, attrs)
  end

  def import_random_users_from_pesel(count \\ 100) when is_integer(count) and count > 0 do
    base = pesel_dir()

    male_first   = read_top_100_first_col(Path.join(base, "imiona_meskie.csv"))
    female_first = read_top_100_first_col(Path.join(base, "imiona_damskie.csv"))
    male_last    = read_top_100_first_col(Path.join(base, "nazwiska_meskie.csv"))
    female_last  = read_top_100_first_col(Path.join(base, "nazwiska_damskie.csv"))

    Enum.reduce(1..count, {0, 0}, fn _, {ok, fail} ->
      attrs = random_user_attrs_from_lists(male_first, female_first, male_last, female_last)

      case create_user(attrs) do
        {:ok, _} -> {ok + 1, fail}
        {:error, _} -> {ok, fail + 1}
      end
    end)
  end

  defp pesel_dir do
    :phoenix_api |> Application.app_dir("priv/pesel")
  end

  defp read_top_100_first_col(path) do
    path
    |> File.stream!()
    |> Stream.map(&String.trim/1)
    |> Stream.reject(&(&1 == ""))
    |> Stream.map(&String.replace(&1, "\uFEFF", "")) # BOM safety
    |> Stream.drop(1) # drop header
    |> Stream.map(fn line ->
      line
      |> String.split(",", parts: 2)
      |> List.first()
      |> String.trim()
    end)
    |> Stream.reject(&(&1 == ""))
    |> Stream.take(100)
    |> Enum.to_list()
  end

  defp random_user_attrs_from_lists(male_first, female_first, male_last, female_last) do
    gender = if :rand.uniform(2) == 1, do: "male", else: "female"

    first_name =
      if gender == "male",
         do: Enum.random(male_first),
         else: Enum.random(female_first)

    last_name =
      if gender == "male",
         do: Enum.random(male_last),
         else: Enum.random(female_last)

    %{
      first_name: String.capitalize(String.downcase(first_name)),
      last_name: String.capitalize(String.downcase(last_name)),
      gender: gender,
      birthdate: random_birthdate(~D[1970-01-01], ~D[2024-12-31])
    }
  end

  defp random_birthdate(from_date, to_date) do
    from_days = Date.to_gregorian_days(from_date)
    to_days = Date.to_gregorian_days(to_date)

    random_days = :rand.uniform(to_days - from_days + 1) - 1
    Date.from_gregorian_days(from_days + random_days)
  end


  defp normalize_params(params) do
    %{
      "first_name" => blank_to_nil(params["first_name"]),
      "last_name" => blank_to_nil(params["last_name"]),
      "gender" => blank_to_nil(params["gender"]),
      "birthdate_from" => parse_date(params["birthdate_from"]),
      "birthdate_to" => parse_date(params["birthdate_to"]),
      "sort_by" => params["sort_by"] || "id",
      "sort_dir" => params["sort_dir"] || "asc"
    }
  end

  defp blank_to_nil(nil), do: nil
  defp blank_to_nil(""), do: nil
  defp blank_to_nil(v), do: v

  defp parse_date(nil), do: nil
  defp parse_date(""), do: nil
  defp parse_date(v) do
    case Date.from_iso8601(v) do
      {:ok, d} -> d
      _ -> nil
    end
  end

  defp apply_filters(query, params) do
    import Ecto.Query, warn: false

    query
    |> maybe_where_ilike(:first_name, params["first_name"])
    |> maybe_where_ilike(:last_name, params["last_name"])
    |> maybe_where_eq(:gender, params["gender"])
    |> maybe_where_date_gte(:birthdate, params["birthdate_from"])
    |> maybe_where_date_lte(:birthdate, params["birthdate_to"])
  end

  defp maybe_where_ilike(query, _field, nil), do: query
  defp maybe_where_ilike(query, field, value) do
    where(query, [u], ilike(field(u, ^field), ^"%#{value}%"))
  end

  defp maybe_where_eq(query, _field, nil), do: query
  defp maybe_where_eq(query, field, value) do
    where(query, [u], field(u, ^field) == ^value)
  end

  defp maybe_where_date_gte(query, _field, nil), do: query
  defp maybe_where_date_gte(query, field, date) do
    where(query, [u], field(u, ^field) >= ^date)
  end

  defp maybe_where_date_lte(query, _field, nil), do: query
  defp maybe_where_date_lte(query, field, date) do
    where(query, [u], field(u, ^field) <= ^date)
  end

  @allowed_sort_fields ~w(id first_name last_name birthdate gender inserted_at updated_at)a
  defp apply_sort(query, %{"sort_by" => sort_by, "sort_dir" => sort_dir}) do
    import Ecto.Query, warn: false

    field_atom =
      case sort_by do
        s when is_binary(s) ->
          try do
            String.to_existing_atom(s)
          rescue
            _ -> :id
          end

        _ -> :id
      end

    field_atom = if field_atom in @allowed_sort_fields, do: field_atom, else: :id
    dir = if sort_dir == "desc", do: :desc, else: :asc

    order_by(query, [u], [{^dir, field(u, ^field_atom)}])
  end

end
