-- ล้างแคตตาล็อก "อุปกรณ์/ซอฟต์แวร์ในร้าน" (ตาราง equipment) ของเก่าทิ้งทั้งหมด
-- ใส่เฉพาะรุ่น/แบรนด์จริงที่ตรงกับ devices ที่ reseed ไปแล้ว (Urovo/TSC/Zebra/Point Mobile/Unitech)
-- ต้องรัน reseed_full.sql (มี partners 5 แบรนด์) ไปก่อนแล้วเท่านั้น ไม่งั้น partner_id จะจับคู่ไม่เจอ

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE equipment;

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'Urovo DT50', 'Urovo', 'DT50', 'hardware', p.id, 'Mobile Computer / Handheld', 1, NOW()
FROM partners p WHERE p.company_name = 'Urovo';

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'Urovo DT50 8GB', 'Urovo', 'DT50 8GB', 'hardware', p.id, 'Mobile Computer / Handheld', 1, NOW()
FROM partners p WHERE p.company_name = 'Urovo';

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'TSC TE210', 'TSC', 'TE210', 'hardware', p.id, 'Barcode / Label Printer', 1, NOW()
FROM partners p WHERE p.company_name = 'TSC';

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'Zebra ZT231 203 dpi', 'Zebra', 'ZT231 203dpi', 'hardware', p.id, 'Barcode / Label Printer', 1, NOW()
FROM partners p WHERE p.company_name = 'Zebra';

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'Point Mobile PM84', 'Point Mobile', 'PM84', 'hardware', p.id, 'Mobile Computer / Handheld', 1, NOW()
FROM partners p WHERE p.company_name = 'Point Mobile';

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'Point Mobile PM84 (P)', 'Point Mobile', 'PM84 (P)', 'hardware', p.id, 'Mobile Computer / Handheld', 1, NOW()
FROM partners p WHERE p.company_name = 'Point Mobile';

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'Point Mobile PM452 WIFI', 'Point Mobile', 'PM452 WIFI', 'hardware', p.id, 'Mobile Computer / Handheld', 1, NOW()
FROM partners p WHERE p.company_name = 'Point Mobile';

INSERT INTO equipment (name, brand, model, device_type, partner_id, description, is_active, created_at)
SELECT 'Unitech PA768', 'Unitech', 'PA768', 'hardware', p.id, 'Mobile Computer / Handheld', 1, NOW()
FROM partners p WHERE p.company_name = 'Unitech';

SET FOREIGN_KEY_CHECKS = 1;

-- ตรวจสอบผล
SELECT e.name, e.brand, e.model, p.company_name AS partner
FROM equipment e
LEFT JOIN partners p ON p.id = e.partner_id
ORDER BY e.brand, e.name;

SELECT COUNT(*) AS total_equipment, SUM(partner_id IS NULL) AS missing_partner FROM equipment;
