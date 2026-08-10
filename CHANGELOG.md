# Changelog

## 2026-08-11 — ปฏิทินซ่อม / ล็อคราคาใบเสนอราคา / จ่ายงานอัตโนมัติ / ส่งต่อ Partner โดยช่าง / หมวดหมู่การซ่อม / อัพเดทรูปให้ลูกค้า

**ขอบเขต:** ชุดฟีเจอร์ใหญ่หลายเรื่องต่อเนื่องกันในเซสชันเดียว ครอบคลุมทั้ง 3 role (admin/technician/partner) ตั้งแต่หน้าประวัติการซ่อม ไปจนถึง workflow มอบหมายงานและแจ้งลูกค้าใหม่ทั้งหมด

### 1. หน้า "ประวัติการซ่อม" เปลี่ยนจากตารางเป็นปฏิทิน (admin + technician)
- Admin: เลือกดูรายช่างได้ทีละคนหรือดูรวม งานที่วันต่อเนื่องกันจะแสดงเป็นแท่งเดียวยาวข้ามวัน (คำนวณ column-span + row-packing เอง คล้าย Google Calendar month view) ไม่ใช้ library ภายนอก
- Technician: ปฏิทินแบบเดียวกันแต่เห็นเฉพาะงานตัวเอง ไม่มีแท็บเลือกช่าง
- ทั้งสองไฟล์กรอง `partner_id IS NULL` ด้วย ไม่งั้นงานที่ช่างส่งต่อ Partner ไปแล้วจะยังโผล่ในปฏิทินช่างเดิม

### 2. ล็อคราคาใบเสนอราคาของ Admin ไม่ให้ต่ำกว่าที่ Partner เสนอ
- `Tickets::save_quotation()` เช็คฝั่งเซิร์ฟเวอร์ ปฏิเสธถ้า `quote_amount < partner_quote_amount`
- `Tickets::quotation()` prefill รายการจาก `partner_quote_detail`/`partner_quote_amount` ให้ Admin เริ่มจากราคาที่ Partner เสนอแทนที่จะเริ่มจาก 0
- แก้บั๊คเดิมที่หน้าฟอร์มโชว์ `$ticket->quote_amount` (ราคา Admin เอง) แต่ label เขียนว่า "ใบเสนอราคาจาก Partner" ผิด

### 3. จ่ายงานให้ช่างแบบอัตโนมัติจากคิวว่าง (Admin)
- ตัดช่องเลือก Partner ออกจากหน้าจ่ายงานของ Admin ทั้งหมด — Admin จ่ายงานให้ "ช่าง" เท่านั้น
- โมดอลคิวช่างจัดอันดับ: ว่างวันนี้ก่อน (เรียงงานค้างน้อยสุด) ตามด้วยช่างที่ติดงาน (เรียงวันที่จะว่างเร็วสุด) ระบบเลือกอันดับ 1 ให้ก่อนแต่ Admin เปลี่ยนคนอื่นได้
- สถานะ `escalated` (Partner ส่งกลับ Admin) ยังคงใช้โมดอลนี้เหมือนเดิม

### 4. ช่างเลือก Partner เองตอนส่งต่อ (เปลี่ยน workflow เดิม admin assign → partner เป็น ช่าง assign → partner)
- `technician/Tickets.php::escalate()` เขียนใหม่ทั้งหมด — ช่างเลือก Partner จาก dropdown เอง พร้อมแนบรูปได้หลายรูป (`images[]` → `uploads/tickets/`, เก็บเป็น JSON ใน `tickets.images`)
- **บั๊คที่พบและแก้ระหว่างทาง:** `technician_id`/`partner_id` ค้างอยู่พร้อมกันได้ ทำให้หน้าช่างเห็นปุ่ม "รับงาน"/"บันทึกผลซ่อม" ของงานที่ส่งต่อ Partner ไปแล้ว → เพิ่มเงื่อนไข `$owned_by_tech` ซ่อนปุ่มเหล่านั้น และให้ `assign_technician()` เคลียร์ `partner_id` ทุกครั้งที่ Admin (re)assign ช่าง

### 5. หมวดหมู่การซ่อม + ล็อควันซ่อม (กันช่างกำหนดวันเอง)
- ตารางใหม่ `repair_categories` (ชื่อ, จำนวนวันสูงสุด) — เพิ่ม CRUD ให้ Admin ที่เมนู "หมวดหมู่การซ่อม" พร้อม 20 รายการตัวอย่าง (ซ่อมตลับหมึก 3 วัน, เปลี่ยน ROM 2 วัน, RMA เคลมประกัน 7 วัน ฯลฯ)
- ช่าง "รับงาน" ต้องเลือกหมวดหมู่แทนกรอกวันเอง ระบบคำนวณ `tech_end_date` ให้อัตโนมัติ; "ปรับหมวดหมู่" ระหว่างซ่อมคำนวณวันใหม่จากวันเริ่มเดิม (วันเริ่มไม่ขยับ) และแจ้งลูกค้าทาง LINE ทุกครั้งที่แก้ พร้อมข้อความ "(ขออนุญาตแก้ไขประเภทการซ่อม)"
- สถานะใหม่ `waiting_parts` (รออะไหล่) — ไม่มี cron ในโปรเจกต์ ใช้ lazy check (`Ticket_model::flag_overdue_repairs()`) ตอนโหลดหน้า list/detail ของทุก role แทน ถ้า `in_progress` เลย `tech_end_date` จะเปลี่ยนสถานะอัตโนมัติ + แจ้งลูกค้า
- **บั๊คที่พบและแก้ระหว่างทาง:** dashboard เผลอไม่รวม `waiting_parts` ในลิสต์/นับ "งานกำลังดำเนินการ" ทำให้งานเกินกำหนดหายไปจาก dashboard เงียบๆ

### 6. ช่างส่งอัพเดทความคืบหน้า (รูป+ข้อความ) ให้ลูกค้าได้หลายครั้งระหว่างซ่อม
- ตารางใหม่ `ticket_updates` — ช่างส่งได้เฉพาะช่วง `status === 'in_progress'` เป๊ะๆ (หมดสิทธิ์ทันทีที่เกินกำหนดหรือส่งต่อ Partner ไปแล้ว)
- `Line_notify::push_update()` ส่งข้อความ+รูป (สูงสุด 4 รูปต่อการ push เพราะ LINE จำกัด 5 ข้อความ/ครั้ง) ไปยัง LINE ของลูกค้าเจ้าของ ticket นั้นๆ โดยเฉพาะ
- `Ticket_model::get_timeline()` รวม `ticket_logs` (เปลี่ยนสถานะ) กับ `ticket_updates` (รูป+ข้อความ) เป็นเส้นเดียวกัน เรียงตามเวลา แสดงบนทั้งหน้า Admin และ Technician

### 7. บั๊คอื่นๆ ที่แก้ระหว่างทาง
- `CI_DB_mysqli_result::order_by()` — เรียก `order_by()` ต่อจาก `get_where()` (ซึ่ง execute query ไปแล้ว) ไม่ได้ ต้องสลับลำดับเป็น `order_by()` ก่อน `get()`
- ลบอุปกรณ์ไม่ได้ตลอดกาลถ้าเคยมี ticket แม้จะปิด/เสร็จไปแล้ว — `Devices::delete()` เดิมนับ ticket ทุกสถานะ แก้ให้นับเฉพาะที่ยังไม่จบ (`completed`/`closed` ไม่นับ)

### 8. เลย์เอาต์หน้ารายละเอียดงาน (technician + partner)
เปลี่ยนจาก 1 คอลัมน์แคบ (`max-w-2xl`/`max-w-3xl`) เป็น 2 คอลัมน์แบบเดียวกับหน้า Admin — ซ้าย (2 ส่วน) = ข้อมูลงาน+ฟอร์มทั้งหมด, ขวา (1 ส่วน) = Timeline ลอยข้างๆ ตลอด ไม่ต้องเลื่อนไปดูข้างล่าง

---

## Schema changes (รันแล้วบน `rtaf_3wsupportv2`)
```sql
CREATE TABLE repair_categories (...);          -- ข้อ 5
ALTER TABLE tickets ADD COLUMN repair_category_id INT UNSIGNED NULL;
ALTER TABLE tickets MODIFY status ENUM(..., 'waiting_parts', ...);  -- ต้อง ALTER ก่อนใช้ ไม่งั้นเก็บเป็นค่าว่างเงียบๆ (ดูบทเรียนเดิมข้อ 84 ด้านล่าง)
CREATE TABLE ticket_updates (...);              -- ข้อ 6
```
ต้องรัน SQL ชุดนี้บนฐานข้อมูลจริงก่อนอัพโค้ดชุดนี้ขึ้นเซิร์ฟเช่นเดียวกับรอบก่อน

---

# Changelog — รอบแก้ไข Workflow ในประกัน/หมดประกัน

**วันที่:** 2026-08-05
**ขอบเขต:** ตรวจสอบและแก้ไข workflow การแจ้งซ่อมผ่าน LINE OA สำหรับตั๋วหมดประกัน (ช่าง/Partner ออกใบเสนอราคา → Admin ดีดราคา → ลูกค้ายืนยันผ่าน LINE) และตั๋วในประกัน (มอบหมายงาน → กำหนดวัน → ซ่อมเสร็จ) รวมถึงบั๊คที่พบระหว่างการทดสอบจริง

---

## สรุปสิ่งที่แก้ไข

### 1. บั๊ค: ตั๋วช่างบริษัท (หมดประกัน) ค้าง ออกใบเสนอราคาไม่ได้
Admin มอบหมายช่างให้ตั๋วหมดประกันแล้วตั้งสถานะเป็น `assigned` ตรงๆ แต่หน้าจอช่างต้องการสถานะ `wait_quote` ถึงจะโชว์ฟอร์มใบเสนอราคา ตั๋วเลยค้าง ไม่มีทางออกใบเสนอราคาได้
**แก้:** ให้เคสหมดประกันของช่างใช้สถานะ `wait_quote` เหมือน Partner ทุกประการ (sync สถานะให้ตรงกัน)

### 2. บั๊ค: ลูกค้ายืนยันราคาข้าม Admin ได้ (ข้ามขั้นตอนดีดราคา)
เดิมทีสถานะ `wait_confirm` ถูกใช้ทั้ง "รอ Admin ตรวจราคา" และ "ส่งลูกค้าแล้วรอยืนยัน" ปนกัน ทำให้ลูกค้าพิมพ์ "ยืนยัน:ID" ทาง LINE ตอนไหนก็ได้ (แม้ Admin ยังไม่ได้ดีดราคา) ก็ยืนยันสำเร็จที่ราคาต้นทุนทันที
**แก้:**
- เพิ่มสถานะใหม่ `wait_review` (รอ Admin ตรวจสอบ/ดีดราคา) แยกจาก `wait_confirm` (ส่งให้ลูกค้าแล้วจริง)
- `wait_confirm` จะเกิดขึ้นได้จุดเดียวคือตอน Admin กด "ส่งให้ลูกค้าผ่าน Line" เท่านั้น
- **พบเพิ่มระหว่างทดสอบจริง:** ปุ่ม "ยืนยัน" ใน LINE Flex Message (ข้อความ `ยืนยัน:ID`) เรียกฟังก์ชันคนละตัว (`_handle_quote_response`) ที่ไม่เช็คสถานะเลย ทำให้บายพาสได้อยู่ดีแม้แก้จุดแรกแล้ว → เพิ่มการเช็คสถานะต้องเป็น `wait_confirm` เท่านั้นในฟังก์ชันนี้ด้วย

### 3. บั๊ค (พบจากการทดสอบจริง ไม่เคยรู้มาก่อน): Partner เข้าตั๋วตัวเองไม่ได้
`Ticket_model::get_by_id()` SELECT ทั้ง `t.*` และ `d.partner_id` (Partner ประจำอุปกรณ์) โดยไม่ตั้งชื่อ column แยกกัน ทำให้ค่า `partner_id` ของตั๋วจริงถูกทับด้วยของอุปกรณ์ ส่งผลให้ Partner ที่ถูก assign เข้าตั๋วตัวเองไม่ได้ (ระบบฟ้อง "ไม่มีสิทธิ์เข้าถึง") ทุกครั้งที่ Partner ที่ assign ไม่ตรงกับ Partner ประจำอุปกรณ์เดิม
**แก้:** ตั้ง alias คอลัมน์แยกเป็น `d.partner_id as device_partner_id`

### 4. Flow ใหม่: หมดประกันต้อง "กำหนดวันคาดว่าจะเสร็จ" หลังลูกค้ายืนยันราคาแล้ว (เหมือนบริบทในประกัน)
เดิมพอลูกค้ายืนยันราคา (`quote_accepted`) ทั้งช่างและ Partner จะกระโดดตรงไปหน้าบันทึกผลซ่อม/ปิดงานเลย ไม่มีขั้นตอนกำหนดวัน
**แก้:** ย้ายฟอร์ม "รับงานและกำหนดวัน" มาอยู่หลัง `quote_accepted` แทน (ใช้ endpoint `accept()` เดิม เข้าสถานะ `in_progress` เดียวกับบริบทในประกัน) พร้อมเติมช่อง "เลขพัสดุ" กลับเข้าไปในฟอร์มบันทึกผลซ่อมของสถานะ `in_progress` (เฉพาะเคสหมดประกัน) เพื่อไม่ให้เสียความสามารถเดิม

### 5. บั๊คเล็ก: Label สถานะ/ราคาที่ไม่ตรงกันระหว่างช่างกับ Partner
- หน้า list ของ Partner ไม่มี label สำหรับสถานะ `assigned` (โชว์คำดิบภาษาอังกฤษ) — เติม label "รอรับงาน"
- หน้า list ของ Partner แสดงราคาผิด ใช้ `quote_amount` (ราคาที่ Admin ดีดแล้ว) แทนที่จะเป็น `partner_quote_amount` (ราคาที่ Partner กรอกเองตอนแรก) — แก้ให้ถูกต้อง
- เติม label `wait_quote` ("รอกรอก Quotation") ให้ฝั่งช่างที่แต่ก่อนไม่เคยมี เพราะสถานะนี้ไม่เคยถูกใช้กับช่างมาก่อน (มีจากข้อ 1)

### 6. บั๊คเล็ก: Log ประวัติบันทึกสถานะผิด
ตอนช่าง/Partner ซ่อมเสร็จเคสหมดประกัน (`complete()`) log ที่บันทึกลง `ticket_logs` เขียน `new_status` เป็น `completed` แบบ hardcode ทั้งที่สถานะจริงที่ถูกตั้งคือ `partner_completed` — แก้ให้ log ตรงกับสถานะจริง (ใช้ `$ticket->status` แทนการ hardcode ด้วย)

---

## ผลการทดสอบ (รันจริงผ่านเบราว์เซอร์ทุกจุด)

ทดสอบ End-to-End ครบ 4 สถานการณ์ ตั้งแต่ Admin อนุมัติ → มอบหมาย → ดำเนินการ → ปิดตั๋ว:

| สถานการณ์ | ผล |
|---|---|
| ในประกัน + ช่าง | ✅ ผ่าน |
| ในประกัน + Partner | ✅ ผ่าน |
| หมดประกัน + ช่าง (รวมทดสอบบล็อกลูกค้ายืนยันก่อนกำหนด) | ✅ ผ่าน |
| หมดประกัน + Partner (รวมทดสอบบล็อกลูกค้ายืนยันก่อนกำหนด) | ✅ ผ่าน |

ยืนยันด้วยว่าราคาสุดท้ายที่ลูกค้ายืนยันตรงกับราคาที่ Admin ดีดขึ้นเสมอ (ไม่ใช่ราคาต้นทุนจากช่าง/Partner) และ audit log (`ticket_logs`) เรียงลำดับสถานะถูกต้องครบทุกขั้นตอน

การทดสอบจำลองการยืนยันผ่าน LINE ด้วยการยิง webhook ตรงพร้อมลายเซ็น HMAC ที่ถูกต้อง (เนื่องจากรันบน localhost ไม่มี LINE จริงมาเรียก) โดยใช้ LINE UID ปลอมที่ไม่ผูกกับบัญชีจริง เพื่อไม่ให้มีข้อความหลุดไปหาใคร

---

## ไฟล์โค้ดที่เปลี่ยนแปลงทั้งหมด (17 ไฟล์)

### Controllers
- `application/controllers/admin/Tickets.php`
- `application/controllers/admin/Dashboard.php`
- `application/controllers/technician/Tickets.php`
- `application/controllers/partner/Tickets.php`
- `application/controllers/api/Line_webhook.php`

### Models
- `application/models/Ticket_model.php`

### Views
- `application/views/admin/tickets/detail.php`
- `application/views/admin/tickets/index.php`
- `application/views/admin/tickets/modal_detail.php`
- `application/views/admin/dashboard.php`
- `application/views/admin/history/index.php`
- `application/views/admin/report/tickets.php`
- `application/views/technician/tickets/detail.php`
- `application/views/technician/tickets/index.php`
- `application/views/partner/tickets/detail.php`
- `application/views/partner/tickets/index.php`

---

## ⚠️ สิ่งที่ต้องทำบนเซิร์ฟก่อน/ตอน Deploy

### 1. ต้องรัน SQL นี้บนฐานข้อมูลจริงก่อนอัพโค้ดใหม่ (สำคัญมาก)
```sql
ALTER TABLE tickets MODIFY status ENUM(
  'pending','approved','assigned','in_progress','wait_quote','wait_review',
  'wait_confirm','quote_accepted','quote_rejected','partner_completed',
  'completed','closed','escalated'
) DEFAULT 'pending';
```
เหตุผล: คอลัมน์ `tickets.status` เป็น ENUM ล็อกค่าตายตัว ถ้าไม่ ALTER ก่อน สถานะใหม่ `wait_review` จะถูกเก็บเป็นค่าว่างเงียบๆ (ไม่มี error ให้เห็น) ทำให้ตั๋วค้างในสถานะผีที่ไม่มีหน้าไหนรองรับ

### 2. ห้ามอัพไฟล์ `application/config/database.php` ทับของจริงบนเซิร์ฟ
ไฟล์นี้ถูกแก้ไว้เพื่อทดสอบในเครื่อง local (XAMPP) เท่านั้น (user/password/ชื่อ database เป็นค่าทดสอบ) เซิร์ฟจริงใช้ค่าของตัวเองอยู่แล้ว แค่เช็คว่า database บนเซิร์ฟมี schema ตรงกับที่โค้ดต้องการ (มีคอลัมน์ `devices.partner_id`, `customers.company_name` และ ENUM ที่ ALTER ไว้ข้างบน)

### 3. Secrets/Tokens ไม่ได้ถูกแตะต้อง
`application/config/app_config.php` (LINE token/secret, OpenRouter API key) ไม่ได้อยู่ในไฟล์ที่เปลี่ยนแปลง ไม่ต้องยุ่งกับมัน ใช้ไฟล์เดิมบนเซิร์ฟได้ตามปกติ

---

## หมายเหตุ (ยังไม่ได้แก้ อยู่นอกขอบเขตรอบนี้)
โปรเจคนี้เป็นงานส่งจบ นศ. ไม่ได้เอาไปใช้จริง จึงยังไม่ได้แก้ประเด็นด้านความปลอดภัยที่เจอระหว่างตรวจโค้ดรอบแรก (เช่น CSRF protection ปิดอยู่, action สำคัญยิงผ่าน GET link, หน้าใบเสนอราคาสาธารณะไม่มีการตรวจสิทธิ์ ฯลฯ) — หากต้องการแก้เพิ่มเติมสามารถแจ้งได้
