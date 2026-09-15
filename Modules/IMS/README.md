# IMS Module Blueprint

`IMS` chua nghiep vu quan ly kho va chi phu thuoc vao `Core`.

## Thu muc chinh

- `Config/`: manifest, routes, filters rieng cua IMS
- `Controllers/`: inventory dashboard, stock movement, master data
- `Database/Migrations/`: bang danh muc va giao dich IMS
- `Database/Seeds/`: seed warehouse defaults, permission mapping
- `Entities/`: item, warehouse, supplier, stock movement line
- `Helpers/`: helper hien thi ton kho, format SKU
- `Libraries/`: stock calculator, document numbering
- `Models/`: item, warehouse, transaction, stock balance
- `Services/`: inventory service, stock posting service, reporting service
- `Views/`: giao dien IMS

## Boundary

- `IMS` chi su dung auth, settings, audit, menu tu `Core`.
- Khong dat code cua `IMS` vao `app/`.
- Neu sau nay can ket noi `LIMS` hoac `QMS`, hay thong qua service/event do `Core` dinh nghia.
