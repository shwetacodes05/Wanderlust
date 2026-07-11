-- Run this ONCE on your live database to fix the duplicate destinations
-- you're seeing in the dropdown (e.g. "Goa" appearing twice).

-- 1. For every destination name that has duplicates, keep the row with
--    the SMALLEST id and delete the rest. If any bookings/reviews point
--    at the row(s) being deleted, re-point them to the row being kept
--    first (see step 1b) so you don't lose booking history.

-- 1a. (Optional but recommended) Re-point bookings/reviews from a
--     duplicate row to the row being kept, before deleting duplicates.
UPDATE bookings b
JOIN destinations dup ON b.destination_id = dup.id
JOIN destinations keep ON keep.name = dup.name AND keep.id < dup.id
SET b.destination_id = keep.id
WHERE dup.id <> keep.id;

UPDATE reviews r
JOIN destinations dup ON r.destination_id = dup.id
JOIN destinations keep ON keep.name = dup.name AND keep.id < dup.id
SET r.destination_id = keep.id
WHERE dup.id <> keep.id;

-- 1b. Now it's safe to delete the duplicate rows.
DELETE dup FROM destinations dup
INNER JOIN destinations keep
  ON dup.name = keep.name AND dup.id > keep.id;

-- 2. Stop this from ever happening again: enforce unique destination names
--    at the database level. If this fails with a duplicate-key error,
--    step 1 above didn't fully clean things up — re-check and re-run it.
ALTER TABLE destinations ADD UNIQUE KEY uniq_destination_name (name);
