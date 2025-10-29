# 🚀 Quick Reference - Setup & Running

## Installation Commands

### Step 1: Fresh Database Setup
```bash
# Remove all migrations and re-run
php artisan migrate:fresh

# Or with seeding in one command
php artisan migrate:fresh --seed
```

### Step 2: Seed All Data (Single Command!)
```bash
php artisan db:seed
```

This creates:
- ✅ 1 Admin account
- ✅ 3 Tariff types
- ✅ 3 Electricity tariffs
- ✅ 11 Substations
- ✅ 15 Buildings
- ✅ 10 Organization units
- ✅ 9 Electric meters
- ✅ 18 Meter readings
- ✅ 12 Hóa đơn
- ✅ 40+ Bill details

## Admin Access

**URL**: http://localhost:8000/admin

**Credentials**:
```
Email: admin@example.com
Password: password
```

## Docker Workflow

### Start Services
```bash
cd docker/environment
docker-compose up -d
```

### Run Artisan Commands
```bash
docker-compose exec cli php artisan migrate
docker-compose exec cli php artisan db:seed
docker-compose exec cli php artisan serve
```

### Stop Services
```bash
docker-compose down
```

## Development

### Build Frontend Assets
```bash
npm run build        # Production
npm run dev         # Development with hot reload
```

### Run Tests
```bash
php artisan test

# With coverage
php artisan test --coverage
```

### Database Commands
```bash
# Show migration status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Reset everything
php artisan migrate:reset

# Fresh + seed
php artisan migrate:refresh --seed
```

## File Structure

```
electric/
├── README.md                    ← Complete project documentation
├── DATABASE_DESIGN.md           ← Database schema & relationships
├── app/
│   ├── Models/                  ← 9 Eloquent models
│   ├── Filament/Resources/      ← 9 Admin panel resources
│   └── Services/                ← Business logic
├── database/
│   ├── migrations/              ← 15+ migrations
│   ├── seeders/
│   │   └── DatabaseSeeder.php   ← All seeding in one file
│   └── factories/               ← 9 model factories
├── resources/
│   ├── css/                     ← Tailwind CSS (professional design)
│   └── js/                      ← Frontend JavaScript
└── docker/
    └── environment/             ← Docker setup
```

## Troubleshooting

### Migrations Fail
```bash
# Check migration status
php artisan migrate:status

# Rollback and try again
php artisan migrate:rollback
php artisan migrate
```

### Seeding Errors
```bash
# Verify models exist
php artisan tinker
>>> App\Models\Bill::count()

# Check seeder syntax
php artisan db:seed --verbose
```

### Database Connection Issues
Verify `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=electric_db
DB_USERNAME=root
DB_PASSWORD=
```

## Key Features

✨ **Single-Command Setup**
- `php artisan migrate:fresh --seed` - Complete fresh setup

📊 **Sample Data**
- 10 organization units (hierarchical)
- 9 electric meters
- 18 meter readings
- 12 bills with realistic amounts
- All relationships working

🎨 **Professional UI**
- Blue primary color
- Slate background
- Border-based design
- 8px rounded corners
- Responsive layout

🔒 **Security**
- CSRF protection
- SQL injection prevention
- XSS protection
- Password hashing
- Input validation

## Performance

- 35+ indexes optimized
- Foreign key constraints enforced
- Eager loading support
- Pagination-ready
- Query optimization included

---

**Version**: 1.0.0 | **Updated**: October 2025
