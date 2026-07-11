# WanderLust — AI Travel Planning Platform

A PHP + MySQL travel planning app with an AI itinerary generator (Groq/Llama), live weather, a smart budget splitter with real distance-based transport costs, a carbon footprint calculator, and a mood-based destination finder.

## Features
- 🤖 AI-generated day-by-day itineraries, grounded in real destination data (not generic guesses)
- 💰 Budget Splitter with real distance-based transport cost estimates (geocoded via OpenStreetMap)
- 🌤️ Live weather lookup
- 🌿 Carbon footprint calculator
- 😊 Mood-based destination finder
- 🔐 Admin panel: manage destinations, bookings, users, enquiries

## Setup

1. **Clone the repo**
   ```bash
   git clone <your-repo-url>
   cd wanderlust
   ```

2. **Create the database**
   - Create a MySQL database named `wanderlust_db` (or your own name).
   - Import the schema:
     ```bash
     mysql -u root -p wanderlust_db < database/wanderlust.sql
     ```

3. **Configure secrets**
   ```bash
   cp config.sample.php config.php
   ```
   Then edit `config.php` with your real DB credentials and API keys:
   - `GROQ_API_KEY` — free at https://console.groq.com/keys
   - `WEATHER_API_KEY` — free at https://openweathermap.org/api

4. **Set folder permissions**
   ```bash
   mkdir -p cache/geocode
   chmod 775 cache/geocode
   ```

5. **Serve the app** (e.g. with PHP's built-in server for local dev)
   ```bash
   php -S localhost:8000
   ```

## Admin login
Default admin account is created by the schema import — check `database/wanderlust.sql` for the seeded admin user, and change the password after first login.

## Notes
- `config.php` is gitignored — never commit real credentials.
- If destinations show up without coordinates in the admin panel, use the 📍 auto-fill button to geocode them (needed for the transport cost feature to work for that destination).
