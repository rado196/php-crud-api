DROP SEQUENCE IF EXISTS "access_tokens_id_seq";
CREATE SEQUENCE "access_tokens_id_seq";

CREATE TABLE IF NOT EXISTS "public"."access_tokens" (
  "id" BIGINT NOT NULL DEFAULT nextval('access_tokens_id_seq') PRIMARY KEY,
  "token" CHARACTER VARYING (255),
  "created_at" TIMESTAMPTZ,
  "updated_at" TIMESTAMPTZ
);
