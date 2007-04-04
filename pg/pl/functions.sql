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

-- Quick and dirty way to fix recompute final score
CREATE OR REPLACE FUNCTION cmat_pl.final_score(mScore numeric, tDeduct numeric, oDeduct numeric)
RETURNS numeric
AS $$
DECLARE
BEGIN
  RETURN mScore - tDeduct - oDeduct;
END;
$$ LANGUAGE plpgsql;

-- Quick and dirty way to fix merited scores for rows that only have 4 scores
CREATE OR REPLACE FUNCTION cmat_pl.merited_score_4(i0 numeric, i1 numeric, i2 numeric, i3 numeric)
RETURNS numeric
AS $$
DECLARE
  lMin numeric;
  lMax numeric;
BEGIN
  lMin := i0;
  IF i1 < lMin THEN
    lMin := i1;
  END IF;
  IF i2 < lMin THEN
    lMin := i2;
  END IF;
  IF i3 < lMin THEN
    lMin := i3;
  END IF;

  lMax := i0;
  IF i1 > lMax THEN
    lMax := i1;
  END IF;
  IF i2 > lMax THEN
    lMax := i2;
  END IF;
  IF i3 > lMax THEN
    lMax := i3;
  END IF;

  RETURN (i0 + i1 + i2 + i3 - lMin - lMax) / 2;

END;
$$ LANGUAGE plpgsql;

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
