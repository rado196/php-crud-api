DROP SEQUENCE IF EXISTS "items_id_seq";
CREATE SEQUENCE "items_id_seq";

CREATE TABLE IF NOT EXISTS "public"."items" (
  "id" BIGINT NOT NULL DEFAULT nextval('items_id_seq') PRIMARY KEY,
  "name" CHARACTER VARYING (255),
  "phone" CHARACTER VARYING (255),
  "key" CHARACTER VARYING (255) NOT NULL,
  "created_at" TIMESTAMPTZ,
  "updated_at" TIMESTAMPTZ
);
