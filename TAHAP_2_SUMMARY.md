# TAHAP 2: Database Migrations + Seeder - COMPLETED ✅

## 📊 Summary

**Total Tables Created**: 20 tabel
**Total Seeders**: 3 seeders
**Status**: ✅ All migrations and seeders executed successfully!

---

## 📋 List of Tables

### 1. **users** (Modified)
- Added columns:
  - `role` (enum: 'admin', 'user')
  - `last_login` (timestamp, nullable)
  - `is_active` (boolean, default: true)

### 2. **countries**
- id, name, code (3 char), region
- currency_code, currency_name, flag_url
- latitude, longitude
- timestamps
- **Indexes**: code, region

### 3. **risk_scores**
- id, country_code
- weather_score, inflation_score, currency_score, news_score
- total_score, risk_level (enum: low, medium, high, critical)
- calculated_at, timestamps
- **Indexes**: country_code, risk_level

### 4. **news_cache**
- id, country_code, title, description, url, source
- sentiment (enum: positive, neutral, negative)
- positive_score, negative_score
- published_at, timestamps
- **Indexes**: country_code, sentiment, published_at

### 5. **ports**
- id, port_name, country_code, country_name
- latitude, longitude, port_type
- is_active (boolean)
- timestamps
- **Indexes**: country_code, is_active
- **Data**: 20 major world ports seeded

### 6. **watchlists**
- id, user_id (foreign), country_code, country_name
- notes (text)
- timestamps
- **Indexes**: user_id, country_code

### 7. **articles**
- id, user_id (foreign), title, content (longtext)
- category (enum: logistics, economy, geopolitics, weather)
- status (enum: draft, published)
- published_at, timestamps
- **Indexes**: user_id, category, status

### 8. **currency_cache**
- id, base_currency (3 char), target_currency (3 char)
- rate (decimal 15,6)
- fetched_at, timestamps
- **Indexes**: base_currency + target_currency

### 9. **weather_cache**
- id, country_code, temperature, rainfall, wind_speed
- weather_condition, risk_level (enum: low, medium, high)
- fetched_at, timestamps
- **Indexes**: country_code, risk_level

### 10. **positive_words**
- id, word (unique)
- timestamps
- **Data**: 28 positive sentiment words seeded

### 11. **negative_words**
- id, word (unique)
- timestamps
- **Data**: 30 negative sentiment words seeded

### 12. **activity_logs**
- id, user_id (foreign, nullable), action, description
- ip_address
- timestamps
- **Indexes**: user_id, created_at

### 13-20. **Laravel Default Tables**
- migrations
- cache, cache_locks
- jobs, job_batches, failed_jobs
- password_reset_tokens
- sessions

---

## 🎯 Seeders Executed

### 1. **DemoUserSeeder**
✅ Created 2 users:
- **Admin**: admin@supply.com / admin123
- **User**: user@supply.com / user123

### 2. **SentimentWordsSeeder**
✅ Inserted sentiment dictionary:
- **Positive words**: 28 words (growth, increase, profit, stable, improve, recovery, boost, expand, strong, success, gain, rise, positive, agreement, peace, export, investment, development, prosperity, advancement, progress, efficient, optimize, partnership, cooperation, innovation, opportunity, benefit)
- **Negative words**: 30 words (war, crisis, inflation, delay, disaster, conflict, shortage, decline, fall, recession, sanction, strike, protest, flood, storm, bankruptcy, disruption, blockage, collapse, threat, violence, uncertainty, risk, damage, loss, failure, problem, emergency, tension, attack)

### 3. **PortsSeeder**
✅ Inserted 20 major world ports:
1. Port of Shanghai (China)
2. Port of Singapore (Singapore)
3. Port of Rotterdam (Netherlands)
4. Port of Los Angeles (USA)
5. Port of Hamburg (Germany)
6. Port of Antwerp (Belgium)
7. Port of Hong Kong (Hong Kong)
8. Port of Busan (South Korea)
9. Port of Dubai (UAE)
10. Port of Tokyo (Japan)
11. Port of Long Beach (USA)
12. Port of Guangzhou (China)
13. Port of Qingdao (China)
14. Port of Tianjin (China)
15. Port of Ningbo (China)
16. Port of Shenzhen (China)
17. Port of Kaohsiung (Taiwan)
18. Port of Tanjung Pelepas (Malaysia)
19. Port of Tanjung Priok (Indonesia)
20. Port of Jebel Ali (UAE)

---

## 🗂️ Migration Files Created

```
database/migrations/
├── 0001_01_01_000000_create_users_table.php (default)
├── 0001_01_01_000001_create_cache_table.php (default)
├── 0001_01_01_000002_create_jobs_table.php (default)
├── 2026_07_13_114645_add_role_to_users_table.php
├── 2026_07_13_121456_add_additional_fields_to_users_table.php
├── 2026_07_13_121629_create_countries_table.php
├── 2026_07_13_121830_create_risk_scores_table.php
├── 2026_07_13_121847_create_news_cache_table.php
├── 2026_07_13_122259_create_ports_table.php
├── 2026_07_13_122315_create_watchlists_table.php
├── 2026_07_13_122324_create_articles_table.php
├── 2026_07_13_122334_create_currency_cache_table.php
├── 2026_07_13_122404_create_weather_cache_table.php
├── 2026_07_13_122413_create_positive_words_table.php
├── 2026_07_13_122422_create_negative_words_table.php
└── 2026_07_13_122430_create_activity_logs_table.php
```

---

## 📝 Seeder Files Created

```
database/seeders/
├── DatabaseSeeder.php (updated)
├── DemoUserSeeder.php (updated)
├── SentimentWordsSeeder.php (new)
└── PortsSeeder.php (new)
```

---

## ✅ Verification

### Commands Executed:
```bash
php artisan migrate:fresh --seed
```

### Results:
✅ All 16 migrations executed successfully
✅ All 3 seeders executed successfully
✅ Total 20 tables created in database
✅ Demo data inserted successfully

### Test Credentials:
```
Admin:
Email: admin@supply.com
Password: admin123

User:
Email: user@supply.com
Password: user123
```

---

## 🎯 Database Schema Features

### ✅ Foreign Keys
- `watchlists.user_id` → `users.id` (cascade delete)
- `articles.user_id` → `users.id` (cascade delete)
- `activity_logs.user_id` → `users.id` (set null)

### ✅ Indexes for Performance
- All foreign keys indexed
- Country codes indexed
- Date/timestamp fields indexed
- Enum fields indexed for filtering

### ✅ Data Types Optimized
- Decimal for precise calculations (scores, coordinates, rates)
- Enum for fixed choices (status, sentiment, risk levels)
- Text/LongText for variable content
- Timestamps for date tracking

---

## 📊 Database Statistics

| Table | Rows Seeded | Purpose |
|-------|-------------|---------|
| users | 2 | Admin and user accounts |
| positive_words | 28 | Sentiment analysis dictionary |
| negative_words | 30 | Sentiment analysis dictionary |
| ports | 20 | Major world ports |
| **Other tables** | 0 | Ready for data population |

---

## 🚀 Next Steps

Database structure is ready! Now you can:

1. ✅ **Create Eloquent Models** for each table
2. ✅ **Implement API Controllers** to populate data
3. ✅ **Build Frontend Features** to display data
4. ✅ **Integrate External APIs** to fetch real-time data

---

## 📸 Verification

To verify tables in phpMyAdmin:
1. Open phpMyAdmin
2. Select database: `supply_chain_db`
3. Check left sidebar - should see 20 tables
4. Browse `users`, `ports`, `positive_words`, `negative_words` tables

Or via command line:
```bash
php artisan tinker
>>> DB::select('SHOW TABLES');
```

---

**Status**: ✅ TAHAP 2 COMPLETED
**Date**: July 13, 2026
**Next Phase**: Ready for TAHAP 3 - Create Eloquent Models & Relationships
