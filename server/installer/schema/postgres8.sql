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
    short_name char(1) NOT NULL,
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
    event_code text UNIQUE
);

/**
 * Competitor
 */
CREATE TABLE cmatic_reg_competitor (
);

/**
 * Group
 */
CREATE TABLE cmatic_reg_group (
);

/**
 * Group Member
 */
CREATE TABLE cmatic_reg_group_member (
);

/**
 * Scoring
 *
 * This is what the whole tournament is about.
 */
CREATE TABLE cmatic_result_scoring (
);

/**
 * Registration
 * ?? Could this be a view?
 * Intended to be a way to double check a competitor's registration and
 * perhaps a way to make badges?
 */
--CREATE TABLE cmatic_result_registration (
--);
