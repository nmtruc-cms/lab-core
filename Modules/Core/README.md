# Core Module Blueprint

`Core` la nen tang dung chung cho tat ca module nghiep vu.

## Thu muc chinh

- `Config/`: manifest module, services, routes, registry config
- `Controllers/`: dashboard va entry controller dung chung
- `Database/Migrations/`: migration dung chung cho nen tang
- `Database/Seeds/`: seed roles, permissions, settings
- `Entities/`: entity dung chung nhu user profile, setting item, audit entry
- `Filters/`: filter dung chung nhu module enabled, tenant scope
- `Helpers/`: helper procedural cho navigation, module metadata
- `Libraries/`: utility va orchestration class dung chung
- `Models/`: model dung chung nhu module registry, audit log
- `Services/`: service registry, menu builder, permission builder
- `Views/`: layout va partial dung chung cho backoffice

## Nguyen tac

- Mọi module nghiep vu phai phu thuoc vao `Core`, khong goi cheo truc tiep nhau.
- Auth, permission, menu, settings, audit log deu di qua `Core`.
- Bat ky service nao co the duoc dung lai cho `IMS`, `LIMS`, `QMS` deu dat o day.
