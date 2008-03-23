/**
 * Configuration Tables
 * --------------------
 */

 /**
 * Age group
 */
CREATE TABLE cmatic_config_age_group (
    age_group_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(1) NOT NULL,
    long_name text NOT NULL
);

/**
 * Division (skill level)
 */
CREATE TABLE cmatic_config_division (
    division_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(1) NOT NULL,
    long_name text NOT NULL
);

/**
 * Sex
 */
CREATE TABLE cmatic_config_sex (
    sex_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(1) NOT NULL,
    long_name text NOT NULL
);

/**
 * Form
 */
CREATE TABLE cmatic_config_form (
    form_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(3) NOT NULL,
    long_name text NOT NULL
);

/**
 * Event
 */
CREATE TABLE cmatic_config_event (
    event_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    division_id integer NOT NULL REFERENCES cmatic_config_division,
    sex_id integer NOT NULL REFERENCES cmatic_config_sex,
    age_group_id integer NOT NULL REFERENCES cmatic_config_age_group,
    form_id integer NOT NULL REFERENCES cmatic_config_form,
    event_code text,
    ring_id integer NOT NULL DEFAULT 0,
    ring_order integer,
    num_competitors integer NOT NULL DEFAULT 0,
    is_finished boolean NOT NULL DEFAULT false,
    UNIQUE (division_id, sex_id, age_group_id, form_id)
);


/**
 * Registration Tables
 * -------------------
 */

/**
 * Competitor
 */
CREATE TABLE cmatic_reg_competitor (
    competitor_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    /* competition */
    last_name text NOT NULL,
    first_name text NOT NULL,
    sex_id int REFERENCES cmatic_config_sex,
    age int,
    division_id int REFERENCES cmatic_config_division,
    weight int,
    /* contact */
    email text,
    phone_1 text,
    phone_2 text,
    street_address text,
    city text,
    state text,
    postal_code text,
    country text,
    /* affiliation */
    school text,
    coach text,
    /* emergency */
    emergency_contact_name text,
    emergency_contact_relation text,
    emergency_contact_phone text,
    /* payment */
    is_early_registration boolean NOT NULL DEFAULT FALSE,
    is_discount_registration boolean NOT NULL FALSE,
    amount_paid numeric(6, 2) NOT NULL DEFAULT 0,
    is_confirmed boolean NOT NULL DEFAULT FALSE,
    /* misc */
    comments text
);

/**
 * Group
 */
CREATE TABLE cmatic_reg_group (
    group_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    name text NOT NULL,
    event_id integer NOT NULL REFERENCES cmatic_config_event
);

/**
 * Group Member
 */
CREATE TABLE cmatic_reg_group_member (
    group_member_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    group_id integer NOT NULL REFERENCES cmatic_reg_group,
    competitor_id integer NOT NULL REFERENCES cmatic_reg_competitor,
    UNIQUE (group_id, competitor_id)
);


/**
 * Scoring Tables
 * --------------
 */

/**
 * Scoring
 *
 * This is what the whole tournament is about.
 */
CREATE TABLE cmatic_result_scoring (
    scoring_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    event_id integer NOT NULL REFERENCES cmatic_config_event,
    competitor_id integer,
    group_id integer,
    performance_order integer,
    judge_0 text,
    judge_1 text,
    judge_2 text,
    judge_3 text,
    judge_4 text,
    judge_5 text,
    score_0 numeric,
    score_1 numeric,
    score_2 numeric,
    score_3 numeric,
    score_4 numeric,
    score_5 numeric,
    seconds integer,
    time_deduction numeric,
    other_deduction numeric,
    final_score numeric,
    tie_breaker_0 numeric,
    tie_breaker_1 numeric,
    tie_breaker_2 numeric,
    placement integer,
    UNIQUE (event_id, competitor_id, group_id)
);

/**
 * Registration
 * ?? Could this be a view?
 * Intended to be a way to double check a competitor's registration and
 * perhaps a way to make badges?
 */
--CREATE TABLE cmatic_result_registration (
--);
