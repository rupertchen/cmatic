CREATE OR REPLACE FUNCTION cmat_pl.next_key(iPrefix char(3))
RETURNS char
AS $$
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = iPrefix;

  IF lNextKey IS NULL THEN
    RAISE EXCEPTION 'Unknown prefix given: %', iPrefix;
  END IF;

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = iPrefix;

  RETURN iPrefix || lpad(to_hex(lNextKey), 17, '0');

END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION cmat_pl.get_contact(iContactId char(20))
RETURNS SETOF cmat_core.contact
AS $$
  SELECT * FROM cmat_core.contact WHERE contact_id = $1;
$$ LANGUAGE SQL;
