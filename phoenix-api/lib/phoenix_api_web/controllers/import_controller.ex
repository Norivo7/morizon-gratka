defmodule PhoenixApiWeb.ImportController do
  use PhoenixApiWeb, :controller

  alias PhoenixApi.Accounts

  def create(conn, _params) do
    {inserted, failed} = Accounts.import_random_users_from_pesel(100)

    json(conn, %{
      inserted: inserted,
      failed: failed
    })
  end
end
