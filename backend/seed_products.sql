-- OnlySport - Full Product Seed Data (68 Soccer Cleats)
-- Run: docker exec -i onlysport_db mysql -u root -ppassword onlysport < backend/seed_products.sql

USE `onlysport`;

-- Ensure series exist
INSERT INTO `series` (`series_id`, `series_name`, `description`)
VALUES
  (1, 'Predator', 'Engineered for precision and ball control. Striking zone rubber fins and aggressive traction.'),
  (2, 'F50', 'Designed for explosive speed and ultra-lightweight acceleration.'),
  (3, 'Copa', 'Crafted from premium leather for pure touch, timeless comfort, and class.')
ON DUPLICATE KEY UPDATE `series_name` = VALUES(`series_name`);

-- Truncate and re-seed all 68 products
DELETE FROM `order_items` WHERE 1;
DELETE FROM `products` WHERE 1;
ALTER TABLE `products` AUTO_INCREMENT = 1;

INSERT INTO `products` (`name`, `imageUrl`, `price`, `series_id`, `ground_type`, `description`) VALUES
-- ═══════════════════════════════════════════════
-- PREDATOR Series (series_id = 1) — 23 items
-- ═══════════════════════════════════════════════
('Predator Elite FT FG', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600', 8500.00, 1, 'FG', 'รองเท้าสเปกท็อป Folded Tongue ออกแบบเพื่อการควบคุมบอลสูงสุด พื้น FG'),
('Predator Elite Laceless FG', 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600', 9000.00, 1, 'FG', 'รุ่นไร้เชือกพรีเมียม เสริมยาง High Definition Grip เพิ่มสปินและควบคุมลูก'),
('Predator Elite FG', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600', 8200.00, 1, 'FG', 'สเปกท็อป Control Frame พื้นยึดเกาะสนามหญ้าธรรมชาติ ครีเอทลูกยิงได้อย่างแม่นยำ'),
('Predator League FG', 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=600', 3500.00, 1, 'FG', 'ราคาเข้าถึงง่ายสำหรับสาย Predator ใส่ซ้อมและแข่งได้สบาย พื้น FG ยึดเกาะดี'),
('Predator League TF', 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600', 3200.00, 1, 'TF', 'Predator สำหรับสนามหญ้าเทียม พื้นปุ่มยาง TF ยึดเกาะสนามหญ้าเทียมเต็มที่'),
('Predator Club FG', 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600', 2200.00, 1, 'FG', 'รุ่นคลับราคาประหยัด เหมาะกับผู้เล่นหน้าใหม่ที่ชอบสไตล์ Predator'),
('Predator Club TF', 'https://images.unsplash.com/photo-1605348532760-6753d2c43329?w=600', 2000.00, 1, 'TF', 'Predator Club สนามหญ้าเทียม ราคาคุ้มค่า ใช้ซ้อมทุกวันได้อย่างมั่นใจ'),
('Predator Accuracy.1 FG', 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600', 7200.00, 1, 'FG', 'รุ่นพิเศษเน้นความแม่นยำ ยาง 3D Elements เสริมทั้งหน้าเท้า ยิงโค้งได้แรงขึ้น'),
('Predator Accuracy.2 FG', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=600', 5200.00, 1, 'FG', 'สเปกกลางสาย Accuracy ยาง Control Elements มีเท็กซ์เจอร์ช่วยบังคับลูก'),
('Predator Accuracy.3 FG', 'https://images.unsplash.com/photo-1562183241-b937e95585b6?w=600', 3800.00, 1, 'FG', 'Accuracy ราคาเข้าถึงง่าย เหมาะกับนักเตะสมัครเล่นที่ชอบสไตล์แม่นยำ'),
('Predator Accuracy.3 TF', 'https://images.unsplash.com/photo-1575537302964-96cd47c06b1b?w=600', 3500.00, 1, 'TF', 'Accuracy TF สนามหญ้าเทียม ใช้เตะฟุตซอลหรือ 7 คนสบาย ๆ'),
('Predator Edge.1 Low FG', 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?w=600', 7800.00, 1, 'FG', 'ทรงหุ้มข้อต่ำ Zone Skin เพิ่มพื้นที่สัมผัสบอล เหมาะกับเพลย์เมกเกอร์'),
('Predator Edge.1 High FG', 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=600', 8000.00, 1, 'FG', 'ทรงหุ้มข้อสูง รองรับข้อเท้าได้ดี เสริมความมั่นใจในการเข้าปะทะ'),
('Predator 24 Pro FG', 'https://images.unsplash.com/photo-1465453869711-7e174808ace9?w=600', 6500.00, 1, 'FG', 'โปรสเปก 2024 ระบบ Aeroframe ลดน้ำหนัก เพิ่มประสิทธิภาพการเตะ'),
('Predator 24 League FG', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600', 3800.00, 1, 'FG', 'ลีกสเปก 2024 สวมใส่สบาย เตะได้ทุกตำแหน่ง'),
('Predator 24 League TF', 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=600', 3500.00, 1, 'TF', 'ลีกสเปก TF 2024 เหมาะกับสนามหญ้าเทียมและฟุตซอลเต็มสนาม'),
('Predator 24 Club FG/MG', 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600', 2200.00, 1, 'FG', 'คลับสเปกใช้ได้ทั้ง FG และ MG คุ้มค่าที่สุดในซีรีส์ 24'),
('Predator GL Pro', 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=600', 4500.00, 1, 'FG', 'สเปกผู้รักษาประตู ถุงมือจับบอลเต็มมือ เสริมยาง URG 2.0 Grip'),
('Predator Freak.3 FG', 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?w=600', 3200.00, 1, 'FG', 'Freak สเปก 3 เท็กซ์เจอร์ Demonskin ช่วยควบคุมลูกในทุกสภาพ'),
('Predator Freak.3 TF', 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600', 3000.00, 1, 'TF', 'Freak TF สำหรับพื้นแข็งและสนามเทียม สวมสบาย ทนทาน'),
('Predator Mutator 20.1 FG', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=600', 6800.00, 1, 'FG', 'รุ่นคลาสสิก Mutator Demonskin 2.0 ผิวสัมผัสบอลแน่นหนึบ'),
('Predator Archive FG', 'https://images.unsplash.com/photo-1605408499391-6368c628ef42?w=600', 5500.00, 1, 'FG', 'รุ่น Archive คอลเลกชั่นพิเศษ ดีไซน์ย้อนยุค วัสดุพรีเมียมทันสมัย'),
('Predator Mania FG Remake', 'https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=600', 9500.00, 1, 'FG', 'รีเมค Mania ตำนานรุ่นอมตะ หนังจิงโจ้พรีเมียม ผลิตจำนวนจำกัด'),

-- ═══════════════════════════════════════════════
-- F50 Series (series_id = 2) — 23 items
-- ═══════════════════════════════════════════════
('F50 Elite FG', 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?w=600', 8000.00, 2, 'FG', 'ตำนานแห่งความเร็วเจเนอเรชันใหม่ น้ำหนักเบาพิเศษ ตอบสนองรวดเร็ว'),
('F50 Elite Laceless FG', 'https://images.unsplash.com/photo-1543508282-6319a3e2621f?w=600', 8500.00, 2, 'FG', 'F50 ไร้เชือก เน้นสปีดสูงสุด ผิว SpeedSkin อากาศพลศาสตร์'),
('F50 Pro FG', 'https://images.unsplash.com/photo-1520256862855-398228c41684?w=600', 5800.00, 2, 'FG', 'โปรสเปกสาย Speed น้ำหนักเบา โครงสร้าง SpeedFrame กระชับเท้า'),
('F50 League FG', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=600', 3500.00, 2, 'FG', 'ลีกสเปกสำหรับนักเตะสายเร็ว ราคาคุ้มค่า ซ้อม+แข่งได้หมด'),
('F50 League TF', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600', 3200.00, 2, 'TF', 'F50 สนามหญ้าเทียม พื้นยาง TF เบาเหมือนไม่ได้ใส่'),
('F50 Club FG', 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600', 2200.00, 2, 'FG', 'คลับสเปก สายสปีด ราคาเบา ๆ เหมาะกับผู้เริ่มต้น'),
('F50 Club TF', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600', 2000.00, 2, 'TF', 'F50 Club TF เข้าถึงง่าย ใส่เตะหญ้าเทียมได้ทุกวัน'),
('F50 Ghosted.1 FG', 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=600', 7500.00, 2, 'FG', 'รุ่นโกสต์ ดีไซน์โปร่งใส SprintFrame ลดน้ำหนัก เร็วสะท้านสนาม'),
('F50 Ghosted.3 FG', 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600', 3500.00, 2, 'FG', 'Ghosted สเปก 3 ดีไซน์สวย ราคาเข้าถึงง่าย ใช้ซ้อมได้'),
('F50 Ghosted.3 TF', 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600', 3200.00, 2, 'TF', 'Ghosted TF สนามหญ้าเทียม ปุ่มยางเกาะสนามดีเยี่ยม'),
('F50 adiZero IV FG', 'https://images.unsplash.com/photo-1605348532760-6753d2c43329?w=600', 5000.00, 2, 'FG', 'adiZero รุ่น IV คลาสสิก น้ำหนัก 165 กรัม เบาที่สุดในรุ่น'),
('F50 adiZero Prime FG', 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600', 7800.00, 2, 'FG', 'adiZero Prime ผลิตจำกัด น้ำหนักเพียง 135 กรัม ทำลายทุกสถิติ'),
('F50 Crazylight FG', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=600', 6500.00, 2, 'FG', 'Crazylight น้ำหนักเบาสุดขีด โครง SprintWeb ลดแรงต้านอากาศ'),
('F50+ Spider FG', 'https://images.unsplash.com/photo-1562183241-b937e95585b6?w=600', 5500.00, 2, 'FG', 'Spider FG คลาสสิก ดีไซน์ล้ำ Sprint Skin บางเฉียบ'),
('F50 24 Elite FG', 'https://images.unsplash.com/photo-1575537302964-96cd47c06b1b?w=600', 8200.00, 2, 'FG', 'F50 เจเนอเรชัน 2024 เทคโนโลยีใหม่ทั้งหมด ปุ่ม Lightlock เกาะดี'),
('F50 24 Pro FG', 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?w=600', 6000.00, 2, 'FG', 'โปรสเปก 2024 SpeedPrint กระชับเท้า ใช้แข่งระดับสูงได้'),
('F50 24 League TF', 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=600', 3200.00, 2, 'TF', 'F50 24 TF ลีกสเปก สนามหญ้าเทียม เบาและยืดหยุ่น'),
('F50 24 Club FG/MG', 'https://images.unsplash.com/photo-1465453869711-7e174808ace9?w=600', 2200.00, 2, 'FG', 'คลับ 2024 ใส่ได้ทั้ง FG/MG คุ้มสุดในซีรีส์ F50 ใหม่'),
('F50 Tunit FG', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600', 4500.00, 2, 'FG', 'Tunit ปุ่มเปลี่ยนได้ สลับระหว่าง FG/SG ปรับให้เข้ากับสนาม'),
('F50 Messi FG', 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=600', 7000.00, 2, 'FG', 'เวอร์ชันเมสซี่ ดีไซน์สีฟ้าสุดเท่ เท็กซ์เจอร์ Dribbletex สุดพิเศษ'),
('F50 X FG Limited', 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600', 9200.00, 2, 'FG', 'รุ่นลิมิเต็ด ผลิตเพียง 500 คู่ทั่วโลก Vaporframe คาร์บอนพิเศษ'),
('F50 Junior FG', 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=600', 1800.00, 2, 'FG', 'สำหรับเยาวชน น้ำหนักเบา ดีไซน์สดใส ปลอดภัยสำหรับนักเตะรุ่นเยาว์'),

-- ═══════════════════════════════════════════════
-- COPA Series (series_id = 3) — 22 items
-- ═══════════════════════════════════════════════
('Copa Pure 2 Elite KT FG', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600', 8200.00, 3, 'FG', 'หนังจิงโจ้พรีเมียม Foldover Tongue สัมผัสบอลชั้นเลิศ ลูกสัมผัสแม่น'),
('Copa Pure 2 Elite FG', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600', 7800.00, 3, 'FG', 'Elite สเปกท็อป หนังนุ่ม สวมสบาย พื้น Firm Ground ปุ่มยึดเกาะ'),
('Copa Pure 2.1 FG', 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?w=600', 7500.00, 3, 'FG', 'สเปก 2.1 หนังแท้เกรดสูง Fusion Skin เพิ่มพื้นที่สัมผัสลูก'),
('Copa Pure 2.2 FG', 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600', 5500.00, 3, 'FG', 'สเปก 2.2 สมดุลระหว่างราคาและคุณภาพ หนังสังเคราะห์พรีเมียม'),
('Copa Pure 2.3 FG', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=600', 3800.00, 3, 'FG', 'สเปก 2.3 ราคาเข้าถึงได้ ยังคงความนุ่มสไตล์ Copa'),
('Copa Pure 2.3 TF', 'https://images.unsplash.com/photo-1605408499391-6368c628ef42?w=600', 3500.00, 3, 'TF', 'Copa TF สนามหญ้าเทียม หนังนุ่มเท้า ปุ่มยางเกาะดี'),
('Copa Pure 2 Club FG', 'https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=600', 2200.00, 3, 'FG', 'Copa Club ราคาประหยัด เริ่มต้นสไตล์หนังแท้ได้ง่าย ๆ'),
('Copa Pure 2 Club TF', 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?w=600', 2000.00, 3, 'TF', 'Copa Club TF เหมาะซ้อม ใช้ทุกวัน สนามเทียมเกาะดี'),
('Copa Gloro FG', 'https://images.unsplash.com/photo-1543508282-6319a3e2621f?w=600', 4200.00, 3, 'FG', 'Gloro คลาสสิก หนังจิงโจ้ทั้งตัว Tongue แบบดั้งเดิม สวมง่าย'),
('Copa Gloro TF', 'https://images.unsplash.com/photo-1520256862855-398228c41684?w=600', 4000.00, 3, 'TF', 'Gloro TF หนังจิงโจ้ ใส่เตะหญ้าเทียมสบาย ทนทานสูง'),
('Copa Gloro SG', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=600', 4200.00, 3, 'SG', 'Gloro SG ปุ่มเหล็กเปลี่ยนได้ เหมาะสนามเปียกและนุ่ม'),
('Copa Mundial FG', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600', 5500.00, 3, 'FG', 'ตำนานตลอดกาล ผลิตมาตั้งแต่ 1979 หนังจิงโจ้ คุณภาพไม่เปลี่ยนแปลง'),
('Copa Mundial Team TF', 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600', 5000.00, 3, 'TF', 'Mundial TF พื้นยางสำหรับหญ้าเทียม คุณภาพตำนาน'),
('Copa Sense.1 FG', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600', 7000.00, 3, 'FG', 'Sense เทคโนโลยี Sensepods รับรู้แรงกดเท้า ปรับทรงอัตโนมัติ'),
('Copa Sense.2 FG', 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=600', 5200.00, 3, 'FG', 'Sense 2 ราคากลาง Softtouch หนังนุ่ม คุมบอลได้ดี'),
('Copa Sense.3 FG', 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600', 3500.00, 3, 'FG', 'Sense 3 เข้าถึงง่าย ยังคง DNA สัมผัสหนังแท้ Copa'),
('Copa Sense.3 TF', 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600', 3200.00, 3, 'TF', 'Sense TF หญ้าเทียม ปุ่มยาง ลดอาการเจ็บเท้า เกาะสนามนุ่ม'),
('Copa 20.1 FG', 'https://images.unsplash.com/photo-1605348532760-6753d2c43329?w=600', 6000.00, 3, 'FG', 'รุ่น 20.1 คลาสสิก ปีที่ Copa ได้ดีไซน์ใหม่ทั้งหมด หนังพรีเมียม'),
('Copa Icon FG', 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600', 6800.00, 3, 'FG', 'Icon รุ่นพิเศษ ดีไซน์ย้อนยุค สีขาวดำอมตะ หนังจิงโจ้แท้'),
('Copa 70Y FG Limited', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=600', 9800.00, 3, 'FG', 'รุ่นฉลอง 70 ปี ผลิตจำนวนจำกัด 1,000 คู่ กล่องพิเศษ ใบรับรอง'),
('Copa Junior FG', 'https://images.unsplash.com/photo-1562183241-b937e95585b6?w=600', 1800.00, 3, 'FG', 'สำหรับเยาวชน สัมผัสหนังนุ่ม ปลอดภัย ดีไซน์น่ารัก เบา');

-- Verify the insert
SELECT COUNT(*) AS total_products FROM products;
SELECT series_id, COUNT(*) AS count FROM products GROUP BY series_id ORDER BY series_id;
