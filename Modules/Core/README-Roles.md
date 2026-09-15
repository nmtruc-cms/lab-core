# Dynamic Role Base with Shield

## Nguyen tac

- Shield van la engine auth va authorization chinh.
- Permission duoc dinh nghia o muc atomic, vi du: `ims.stock.receive`.
- Role la tap hop permission, duoc luu vao `AuthGroups.groups` va `AuthGroups.matrix` thong qua `setting()`.
- Lab co the doi ten role, them role, bo role ma khong can sua code check quyen.

## Vi sao cach nay hop voi Shield

- `auth()->user()->can('ims.stock.receive')` van dung nguyen ban.
- `UserModel::addToDefaultGroup()` van dung `setting('AuthGroups.defaultGroup')`.
- Route filter `permission:ims.stock.receive` va `group:lab_manager` van hop le.

## Role base mac dinh

- `director`
- `lab_manager`
- `qa`
- `sales`
- `service`
- `lab_technician`
- `lab_assistant`

## Demo users

Seeder tao san cac tai khoan:

- `super_user@lab-core.com`
- `lab_manager@lab-core.com`
- `director@lab-core.com`
- `qa@lab-core.com`
- `sales@lab-core.com`
- `service@lab-core.com`
- `lab_technician@lab-core.com`
- `lab_assistant@lab-core.com`

Mat khau mac dinh cho moi tai khoan: `123456`

## Cach bootstrap

Chay seeder:

`php spark db:seed Modules\\Core\\Database\\Seeds\\AuthRoleBaseSeeder`

Tao role va user demo:

`php spark db:seed Modules\\Core\\Database\\Seeds\\AuthDemoUsersSeeder`

Seeder se ghi de `AuthGroups.groups`, `AuthGroups.permissions`, `AuthGroups.matrix`, `AuthGroups.defaultGroup` vao bang `settings`.

## Mo rong

- Neu mot lab muon doi role `qa` thanh `quality_manager`, chi can cap nhat groups va matrix qua `RoleManager`.
- Neu them module moi nhu `LIMS`, chi can bo sung permission catalog theo prefix `lims.*`.
- Neu sau nay can mot he thong nhieu lab trong cung mot database, can nang cap sang tenant-aware authorization layer vi Settings cua Shield mac dinh la global cho toan deployment.
