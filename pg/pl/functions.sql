create or replace function cmat_pl.test() returns character as '
declare
	a := \'30\';
begin
return a;
end;
' language plpgsql;

-- This is weird, but we're migrating to Postgres 7 (from 8) and
-- 7 doesn't support arguments in the functions, so we're doing a
-- quick switcheroo to get things to work.
CREATE OR REPLACE FUNCTION cmat_pl.next_key_a01()
RETURNS char AS '
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = \'a01\';

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = \'a01\';

  RETURN \'a01\' || lpad(to_hex(lNextKey), 17, \'0\');
END;
' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cmat_pl.next_key_a02()
RETURNS char AS '
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = \'a02\';

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = \'a02\';

  RETURN \'a02\' || lpad(to_hex(lNextKey), 17, \'0\');
END;
' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cmat_pl.next_key_a03()
RETURNS char AS '
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = \'a03\';

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = \'a03\';

  RETURN \'a03\' || lpad(to_hex(lNextKey), 17, \'0\');
END;
' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cmat_pl.next_key_a04()
RETURNS char AS '
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = \'a04\';

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = \'a04\';

  RETURN \'a04\' || lpad(to_hex(lNextKey), 17, \'0\');
END;
' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cmat_pl.next_key_a05()
RETURNS char AS '
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = \'a05\';

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = \'a05\';

  RETURN \'a05\' || lpad(to_hex(lNextKey), 17, \'0\');
END;
' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cmat_pl.next_key_a06()
RETURNS char AS '
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = \'a06\';

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = \'a06\';

  RETURN \'a06\' || lpad(to_hex(lNextKey), 17, \'0\');
END;
' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cmat_pl.next_key_a07()
RETURNS char AS '
DECLARE
  lNextKey integer;
BEGIN
  SELECT counter
  INTO lNextKey
  FROM cmat_core.next_key
  WHERE prefix = \'a07\';

  UPDATE cmat_core.next_key
  SET counter = counter + 1
  WHERE prefix = \'a07\';

  RETURN \'a07\' || lpad(to_hex(lNextKey), 17, \'0\');
END;
' LANGUAGE plpgsql;

/*
CREATE OR REPLACE FUNCTION cmat_pl.next_key(iPrefix character(3))
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
*/
