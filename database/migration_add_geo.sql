-- Run this once against your existing wanderlust_db database.
-- Adds latitude/longitude so transport cost can be calculated by real
-- distance instead of being a flat guess.

ALTER TABLE destinations
    ADD COLUMN latitude DECIMAL(9,6) NULL AFTER avg_cost_per_day,
    ADD COLUMN longitude DECIMAL(9,6) NULL AFTER latitude;

-- Seed known destinations with real coordinates (safe to re-run; only
-- updates rows that match by name).
UPDATE destinations SET latitude=15.2993, longitude=74.1240 WHERE name='Goa';
UPDATE destinations SET latitude=32.2432, longitude=77.1892 WHERE name='Manali';
UPDATE destinations SET latitude=9.4981,  longitude=76.3388 WHERE name='Kerala Backwaters';
UPDATE destinations SET latitude=26.9124, longitude=75.7873 WHERE name='Rajasthan';
UPDATE destinations SET latitude=11.6234, longitude=92.7265 WHERE name='Andaman Islands';
UPDATE destinations SET latitude=34.1526, longitude=77.5771 WHERE name='Leh Ladakh';
UPDATE destinations SET latitude=12.3375, longitude=75.8069 WHERE name='Coorg';
UPDATE destinations SET latitude=25.3176, longitude=82.9739 WHERE name='Varanasi';
UPDATE destinations SET latitude=30.0869, longitude=78.2676 WHERE name='Rishikesh';
UPDATE destinations SET latitude=13.6288, longitude=79.4192 WHERE name='Tirupati';
UPDATE destinations SET latitude=26.9124, longitude=75.7873 WHERE name='Jaipur';
UPDATE destinations SET latitude=11.4102, longitude=76.6950 WHERE name='Ooty';
UPDATE destinations SET latitude=31.1048, longitude=77.1734 WHERE name='Shimla';

-- Any destination added later through the admin panel needs its lat/lng
-- filled in the same way (the Add Destination form now has fields for it).
