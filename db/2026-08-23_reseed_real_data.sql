-- ============================================================
-- 3wsupport: เปลี่ยนข้อมูลอ้างอิงทั้งหมดเป็นข้อมูลจริง (ลูกค้า/อุปกรณ์/Partner)
-- + หมวดหมู่การซ่อมใหม่เฉพาะ Mobile Computer/Handheld และ Barcode/Label Printer
-- + คอลัมน์ keywords สำหรับ auto-match จากอาการที่ลูกค้าแจ้ง
-- คงไว้: users, technicians, faq -- ไม่แตะ
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------- 0) เพิ่มคอลัมน์ keywords (สำหรับ auto-match อาการ) ----------
ALTER TABLE repair_categories ADD COLUMN IF NOT EXISTS keywords TEXT NULL DEFAULT NULL AFTER max_days;

-- ---------- 1) ล้างข้อมูลเก่าทั้งหมด ----------
TRUNCATE TABLE ticket_updates;
TRUNCATE TABLE ticket_logs;
TRUNCATE TABLE quotations;
TRUNCATE TABLE line_repair_pending;
TRUNCATE TABLE line_waiting_sn;
TRUNCATE TABLE line_verify;
TRUNCATE TABLE tickets;
TRUNCATE TABLE devices;
TRUNCATE TABLE customers;
TRUNCATE TABLE partners;
TRUNCATE TABLE repair_categories;

-- ---------- 2) หมวดหมู่การซ่อมใหม่ (เฉพาะที่เกี่ยวกับอุปกรณ์จริง) ----------
INSERT INTO repair_categories (name, max_days, keywords, is_active, created_at) VALUES
('เปลี่ยนจอสัมผัส (Touch Screen)', 4, 'จอดับ,จอไม่ติด,จอแตก,จอไม่ตอบสนอง,หน้าจอ,สัมผัสไม่ได้,จอค้าง,จอมีเส้น', 1, NOW()),
('เปลี่ยนแบตเตอรี่', 2, 'แบตเสื่อม,แบตหมดเร็ว,ชาร์จไม่เข้า,แบตเตอรี่,เปิดไม่ติด,ไฟไม่เข้า,เครื่องดับเอง', 1, NOW()),
('เปลี่ยนชุดสแกนเนอร์ (Scan Engine)', 4, 'สแกนไม่ติด,ยิงบาร์โค้ดไม่ออก,สแกนบาร์โค้ดไม่ได้,เลเซอร์ไม่ออก,อ่านโค้ดไม่ได้,สแกนเนอร์,ยิงไม่ติด', 1, NOW()),
('ซ่อมพอร์ตชาร์จ / Dock Connector', 2, 'ชาร์จไม่เข้า,พอร์ตชาร์จ,สายชาร์จ,ต่อ dock ไม่ติด,usb เสีย,เสียบชาร์จแล้วไม่เข้า', 1, NOW()),
('เปลี่ยนปุ่มกด / Keypad', 2, 'ปุ่มกดไม่ทำงาน,ปุ่มค้าง,กดไม่ติด,คีย์บอร์ดเสีย,ปุ่มเสีย,ปุ่มไม่ตอบสนอง', 1, NOW()),
('รีเซ็ต / อัปเดตเฟิร์มแวร์ระบบ', 1, 'ค้าง,แฮงค์,รีสตาร์ทเอง,ระบบค้าง,เปิดไม่ขึ้น,บูทไม่ขึ้น,ค้างที่โลโก้', 1, NOW()),
('เปลี่ยนฝาหลัง / บอดี้เครื่อง (ตกแตก)', 3, 'ตก,แตก,บิ่น,ฝาหลังหลุด,บอดี้แตก,หล่น,ร้าว,ตกพื้น', 1, NOW()),
('ซ่อมเมนบอร์ด / วงจรไฟฟ้า', 5, 'เปิดไม่ติดเลย,ไฟไม่เข้าเลย,ช็อต,ไหม้,เสียบสายแล้วไม่มีไฟ,กลิ่นไหม้', 1, NOW()),
('เปลี่ยนหัวพิมพ์ (Print Head)', 3, 'พิมพ์ไม่ออก,ปริ้นไม่ออก,ลายพิมพ์จาง,พิมพ์เป็นเส้น,พิมพ์ไม่ชัด,หัวพิมพ์,พิมพ์ขาดหาย', 1, NOW()),
('เปลี่ยนลูกกลิ้ง / Platen Roller', 2, 'กระดาษไม่เดิน,ป้ายไม่ดึง,ดึงป้ายไม่ผ่าน,กระดาษติด,ลูกกลิ้ง,สติกเกอร์ไม่ออก,ป้ายไม่ออก', 1, NOW()),
('เปลี่ยน/ปรับเซนเซอร์ตรวจจับป้าย', 2, 'พิมพ์เลื่อนตำแหน่ง,ตัดป้ายผิดตำแหน่ง,เซนเซอร์,จับป้ายไม่ได้,พิมพ์เหลื่อม,ตำแหน่งเพี้ยน', 1, NOW()),
('ปรับเทียบ / คาลิเบรทเครื่องพิมพ์', 1, 'พิมพ์เพี้ยน,ขนาดป้ายผิด,คาลิเบรท,ตั้งค่าใหม่,พิมพ์ไม่ตรงป้าย,ขนาดผิด', 1, NOW()),
('เปลี่ยนพาวเวอร์ซัพพลาย / อะแดปเตอร์', 3, 'เปิดไม่ติด,ไฟไม่เข้าเครื่องพิมพ์,adapter เสีย,สายไฟเสีย,เครื่องพิมพ์ไม่ติด,ไฟไม่เข้า', 1, NOW()),
('ทำความสะอาดเครื่อง / หัวพิมพ์', 1, 'มีคราบ,สกปรก,พิมพ์เลอะ,มีเขม่า,พิมพ์มีรอย,ฝุ่นเยอะ', 1, NOW()),
('ส่งเคลมประกันกับ Partner (RMA)', 7, 'เคลมประกัน,ส่งเคลม,rma,ซ่อมไม่ได้,เกินความสามารถ', 1, NOW()),
('ตรวจเช็คทั่วไป / ยังไม่ทราบสาเหตุ', 2, NULL, 1, NOW());

-- ---------- 3) Partner ใหม่ (เจ้าของแบรนด์อุปกรณ์ที่ลูกค้าใช้จริง) ----------
INSERT INTO partners (company_name, is_active, created_at) VALUES
('Urovo', 1, NOW()),
('TSC', 1, NOW()),
('Zebra', 1, NOW()),
('Point Mobile', 1, NOW()),
('Unitech', 1, NOW());

-- ---------- 4) ลูกค้าใหม่ (จากเอกสารจริง -- เบอร์โทรเป็นเลขสมมติ ของจริงไม่มีในเอกสาร กรุณาแก้ทีหลัง) ----------
INSERT INTO customers (company_name, phone, created_at) VALUES
('บริษัท รี เอ็กซ์ โปรดักส์ จำกัด', '02-100-2000', NOW()),
('บริษัท แปซิฟิกไพพ์ จำกัด (มหาชน)', '02-111-2137', NOW()),
('บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด', '02-122-2274', NOW()),
('บริษัท ลาซาด้า เอ็กซ์เพรส จำกัด', '02-133-2411', NOW()),
('บริษัท วนชัย กรุ๊ป จำกัด (มหาชน)', '02-144-2548', NOW());

-- ---------- 5) อุปกรณ์ 50 เครื่อง (10 รุ่น/ลูกค้า x 5 เครื่อง) ----------
-- หมายเหตุ: purchase_date/warranty_end เป็นการสมมติ (ไม่มีในเอกสารต้นฉบับ) -- สุ่มช่วง 3 ปีที่ผ่านมา + ประกัน 2 ปี
-- ทำให้บางเครื่องยังในประกัน บางเครื่องหมดประกันแล้ว ปรับแก้ตามจริงได้ทีหลัง
-- ผูก partner_id ตามเจ้าของแบรนด์จริงของรุ่นนั้น (Urovo/TSC/Zebra/Point Mobile/Unitech)

INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50', '01602437019176', 'hardware', '2024-08-30', '2026-08-30', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50', '01602437019179', 'hardware', '2025-08-20', '2027-08-20', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50', '01602437019175', 'hardware', '2024-04-07', '2026-04-07', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50', '01602437019186', 'hardware', '2026-03-18', '2028-03-17', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50', '01602437019190', 'hardware', '2026-01-27', '2028-01-27', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA23513747', 'hardware', '2025-12-14', '2027-12-14', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA24390444', 'hardware', '2024-06-06', '2026-06-06', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA25340304', 'hardware', '2026-02-26', '2028-02-26', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA23513767', 'hardware', '2025-04-11', '2027-04-11', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA23513785', 'hardware', '2026-04-09', '2028-04-08', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50 8GB', '01602536058292', 'hardware', '2025-12-30', '2027-12-30', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50 8GB', '01602536058027', 'hardware', '2024-01-18', '2026-01-17', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50 8GB', '01602536058285', 'hardware', '2024-02-19', '2026-02-18', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50 8GB', '01602536058309', 'hardware', '2026-02-01', '2028-02-01', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Urovo DT50 8GB', '01602536058320', 'hardware', '2025-02-17', '2027-02-17', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Urovo';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Zebra ZT231 203 dpi', 'T3J260302034', 'hardware', '2025-12-21', '2027-12-21', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Zebra';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Zebra ZT231 203 dpi', 'T3J260302036', 'hardware', '2024-02-06', '2026-02-05', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Zebra';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Zebra ZT231 203 dpi', 'T3J260302043', 'hardware', '2026-02-23', '2028-02-23', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Zebra';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Zebra ZT231 203 dpi', 'T3J260302080', 'hardware', '2025-10-14', '2027-10-14', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Zebra';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Zebra ZT231 203 dpi', 'T3J260302088', 'hardware', '2025-03-24', '2027-03-24', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท รี เอ็กซ์ โปรดักส์ จำกัด' AND p.company_name = 'Zebra';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84', '24076A0077', 'hardware', '2026-02-18', '2028-02-18', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท แปซิฟิกไพพ์ จำกัด (มหาชน)' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84', '24076A0089', 'hardware', '2024-04-03', '2026-04-03', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท แปซิฟิกไพพ์ จำกัด (มหาชน)' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84', '24076A0097', 'hardware', '2026-03-15', '2028-03-14', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท แปซิฟิกไพพ์ จำกัด (มหาชน)' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84', '24076A0117', 'hardware', '2025-03-29', '2027-03-29', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท แปซิฟิกไพพ์ จำกัด (มหาชน)' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84', '24076A0147', 'hardware', '2026-03-21', '2028-03-20', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท แปซิฟิกไพพ์ จำกัด (มหาชน)' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84 (P)', '26075A0260', 'hardware', '2025-09-25', '2027-09-25', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84 (P)', '26075A0266', 'hardware', '2024-11-08', '2026-11-08', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84 (P)', '26075A0272', 'hardware', '2024-02-17', '2026-02-16', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84 (P)', '26075A0265', 'hardware', '2025-09-02', '2027-09-02', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM84 (P)', '26075A0269', 'hardware', '2025-10-26', '2027-10-26', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM452 WIFI', '2535410492', 'hardware', '2024-10-01', '2026-10-01', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM452 WIFI', '2535410495', 'hardware', '2025-06-19', '2027-06-19', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM452 WIFI', '2535410504', 'hardware', '2025-11-25', '2027-11-25', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM452 WIFI', '2535410500', 'hardware', '2025-06-05', '2027-06-05', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Point Mobile PM452 WIFI', '2535410516', 'hardware', '2024-05-23', '2026-05-23', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'Point Mobile';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA25340142', 'hardware', '2025-12-07', '2027-12-07', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA25340149', 'hardware', '2026-02-16', '2028-02-16', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA25340180', 'hardware', '2026-02-22', '2028-02-22', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA25340159', 'hardware', '2025-04-29', '2027-04-29', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA25340194', 'hardware', '2023-09-12', '2025-09-11', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท โอเร็กซ์ เทรดดิ้ง จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA24171038', 'hardware', '2024-01-31', '2026-01-30', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท ลาซาด้า เอ็กซ์เพรส จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA24171077', 'hardware', '2024-09-19', '2026-09-19', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท ลาซาด้า เอ็กซ์เพรส จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA24251016', 'hardware', '2023-11-14', '2025-11-13', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท ลาซาด้า เอ็กซ์เพรส จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA24171042', 'hardware', '2023-12-09', '2025-12-08', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท ลาซาด้า เอ็กซ์เพรส จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'TSC TE210', 'TEA24171080', 'hardware', '2024-06-14', '2026-06-14', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท ลาซาด้า เอ็กซ์เพรส จำกัด' AND p.company_name = 'TSC';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Unitech PA768', '138730240001', 'hardware', '2024-10-19', '2026-10-19', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท วนชัย กรุ๊ป จำกัด (มหาชน)' AND p.company_name = 'Unitech';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Unitech PA768', '138730240002', 'hardware', '2025-02-01', '2027-02-01', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท วนชัย กรุ๊ป จำกัด (มหาชน)' AND p.company_name = 'Unitech';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Unitech PA768', '138730240003', 'hardware', '2025-06-21', '2027-06-21', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท วนชัย กรุ๊ป จำกัด (มหาชน)' AND p.company_name = 'Unitech';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Unitech PA768', '138730240016', 'hardware', '2025-02-10', '2027-02-10', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท วนชัย กรุ๊ป จำกัด (มหาชน)' AND p.company_name = 'Unitech';
INSERT INTO devices (customer_id, partner_id, name, serial_number, device_type, purchase_date, warranty_end, created_at)
SELECT c.id, p.id, 'Unitech PA768', '138730240053', 'hardware', '2026-01-08', '2028-01-08', NOW()
FROM customers c, partners p WHERE c.company_name = 'บริษัท วนชัย กรุ๊ป จำกัด (มหาชน)' AND p.company_name = 'Unitech';

SET FOREIGN_KEY_CHECKS = 1;

-- ---------- สรุปตรวจสอบ ----------
SELECT (SELECT COUNT(*) FROM customers) AS customers,
       (SELECT COUNT(*) FROM partners) AS partners,
       (SELECT COUNT(*) FROM devices) AS devices,
       (SELECT COUNT(*) FROM repair_categories) AS repair_categories,
       (SELECT COUNT(*) FROM tickets) AS tickets;
