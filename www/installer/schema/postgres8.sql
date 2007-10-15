 /**
 * Age group
 */
DROP TABLE /*installer_prefix*/config_age_group;
CREATE TABLE /*installer_prefix*/config_age_group (
    age_group_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(1) NOT NULL,
    long_name text NOT NULL
);

/**
 * Division (skill level)
 */
DROP TABLE /*installer_prefix*/config_division;
CREATE TABLE /*installer_prefix*/config_division (
    division_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(1) NOT NULL,
    long_name text NOT NULL
);

/**
 * Sex
 */
DROP TABLE /*installer_prefix*/config_sex;
CREATE TABLE /*installer_prefix*/config_sex (
    sex_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(1) NOT NULL,
    long_name text NOT NULL
);

/**
 * Form
 */
DROP TABLE /*installer_prefix*/config_form;
CREATE TABLE /*installer_prefix*/config_form (
    form_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    short_name char(1) NOT NULL,
    long_name text NOT NULL
);

/**
 * Event
 */
DROP TABLE /*installer_prefix*/config_event;
CREATE TABLE /*installer_prefix*/config_event (
    event_id serial PRIMARY KEY,
    created timestamp NOT NULL DEFAULT now(),
    last_mod timestamp NOT NULL DEFAULT now(),
    division_id integer REFERENCES config_division,
    sex_id integer REFERENCES config_sex,
    age_group_id integer REFERENCES config_age_group,
    form_id integer REFERENCES config_form,
    event_code text UNIQUE
);

/**
 * Competitor
 */
DROP TABLE /*installer_prefix*/reg_competitor;
CREATE TABLE /*installer_prefix*/reg_competitor (
);

/**
 * Group
 */
DROP TABLE /*installer_prefix*/reg_group;
CREATE TABLE /*installer_prefix*/reg_group (
);

/**
 * Group Member
 */
DROP TABLE /*installer_prefix*/reg_group_member;
CREATE TABLE /*installer_prefix*/reg_group_member (
);

/**
 * Scoring
 *
 * This is what the whole tournament is about.
 */
DROP TABLE /*installer_prefix*/result_scoring;
CREATE TABLE /*installer_prefix*/result_scoring (
);

/**
 * Registration
 * ?? Could this be a view?
 * Intended to be a way to double check a competitor's registration and
 * perhaps a way to make badges?
 */
--CREATE TABLE /*installer_prefix*/result_registration (
--);
