-- OnlySport - Product Ratings Seed Data (56 Reviews)
-- Run in phpMyAdmin: http://localhost:8080/index.php?route=/sql&db=onlysport

USE `onlysport`;

-- 1. สร้างโครงสร้างตาราง product_ratings
CREATE TABLE IF NOT EXISTS `product_ratings` (
  `rating_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `reviewer_name` VARCHAR(150) NULL,
  `score` INT NOT NULL DEFAULT 5,
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_rating_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. เคลียร์ข้อมูลเดิมและเพิ่ม 56 รีวิว
TRUNCATE TABLE `product_ratings`;

INSERT INTO `product_ratings` (`rating_id`, `product_id`, `reviewer_name`, `score`, `comment`, `created_at`) VALUES
(1, 1, 'Mandy25', 5, 'Great all rounder football boots. Great colours to stand out on the field and very comfortable at a great price.', '2026-11-01 04:01:00'),
(2, 2, 'Alex', 5, 'Super comfy, son loves them. Great value for money.', '2026-11-01 04:01:00'),
(3, 3, 'idkidc', 5, 'best shoes ever. lovely! great football shoes.These shoes filled my heart with love and joy.Best shoes ever.', '2026-11-01 04:01:00'),
(4, 4, 'Geminem', 5, 'Great purchase. Bought for my nephew, he absolutely loves the colour.', '2026-11-01 04:01:00'),
(5, 5, 'Sumy', 4, 'Good purchase. A bit tight so need to size up by about 1/2 a size but good boots for weekly practice!', '2026-11-01 04:01:00'),
(6, 6, 'Hazza', 5, 'Good boots for the price. A Softer tongue would improve the comfort overall.', '2026-11-01 04:01:00'),
(7, 7, 'M39M', 5, 'V happy customer. My son was v happy with these. Comfortable and stylish. As always delivery from adidas was speedy and well communicated.', '2026-11-01 04:01:00'),
(8, 8, 'Kt85', 5, 'Nice. Lovely colours, clean up nice. No problems with comfort or fit! Successful buy.', '2026-11-01 04:01:00'),
(9, 9, 'Tim', 5, 'Great value. Daughter needed them for school, they look good and fit well. Great value for money.', '2026-11-01 04:01:00'),
(10, 10, 'Jacob17', 5, 'My son loves them. Lovely football boots for my son. Great colours too, very comfortable and durable.', '2026-11-01 04:01:00'),
(11, 11, 'JEM11', 5, 'GREAT quality materials for the price point! Didn\'t expect much in terms of good feeling materials, but was pleasantly impressed. They feel great!', '2026-11-01 04:01:00'),
(12, 12, 'Footymum', 5, 'Much better fit than the laceless F50\'s. The boot feels a little wider across the toes and laces stay tied the full match every time.', '2026-11-01 04:01:00'),
(13, 13, 'Kea', 1, 'Boots look great but they are very narrow. My son doesn\'t even have a wide foot but these were too narrow and uncomfortable for him.', '2026-11-01 04:01:00'),
(14, 14, 'CopaKing', 5, 'Best youth cleats. Supple so don\'t take forever to break in. These Copas are perfect.', '2026-11-01 04:01:00'),
(15, 15, 'edboy', 5, 'the quality of the boots is good. nice leather. One of the best football boots I buy my boys as they are comfortable and hard wearing.', '2026-11-01 04:01:00'),
(16, 16, 'Dave', 5, 'Good fitting boot and cleans well. Black laces don\'t show the mud and boots look great even after a muddy match.', '2026-11-01 04:01:00'),
(17, 17, 'Luce1418', 5, 'son loves them, perfect for his school boots. sleek style, perfect with subtle enough colour to get away with for school.', '2026-11-01 04:01:00'),
(18, 18, 'Rno79', 4, 'great product! fits my son\'s size perfectly. little caveat: check laces later during game.', '2026-11-01 04:01:00'),
(19, 19, 'MrsH', 4, 'Daughter loves them, fit well and look good. Comfy, laces stay tied and no complaints so far!', '2026-11-01 04:01:00'),
(20, 20, 'Bobsy', 5, 'Big thumbs up from my boy. Bought these for my eldest - aside from great price the boots are quality too. all around great boot.', '2026-11-01 04:01:00'),
(21, 21, 'BillKings', 4, 'Solid boot, really comfy, great for all foot sizes. Molds to your feet pretty well right out of box. Little to no tightness.', '2026-11-01 04:01:00'),
(22, 22, 'MikeV', 4, 'Comfortable and stylish. These are my youngest son\'s favorite style cleats because of the comfort and style. True to size.', '2026-11-01 04:01:00'),
(23, 23, 'Alexa20', 5, 'Style looks really cool!! The cleats are nice and comfortable. The design looks cool and they stand out.', '2026-11-01 04:01:00'),
(24, 24, 'Chanclatazo19', 5, 'For the price & for wide feet these are great! Great quality boot for the price & great for kicking around.', '2026-11-01 04:01:00'),
(25, 25, 'McLovin', 3, 'Sized up 1/2 size and toe area still pretty tight. Fit was snug, toe area is too tight even though i have narrow feet.', '2026-11-01 04:01:00'),
(26, 26, 'Peter_vp', 5, 'very comfortable, good for wide feet, lightweight. didn\'t break yet so they are good quality. highly recommend.', '2026-11-01 04:01:00'),
(27, 27, 'BillKings', 5, 'Solid boot. comfortable, molds to foot well right out of box, good stud pattern, conical studs feel great.', '2026-11-01 04:01:00'),
(28, 28, 'shopee.ph Shopper', 5, 'Great for slightly wide feet. They feel comfortable and fit nicely without being too tight. I really love the look and design.', '2026-11-01 04:01:00'),
(29, 29, 'Jackson', 4, 'Great cleats. After I bought heel pads there was no slipping and it felt really good. Fast and lightweight.', '2026-11-01 04:01:00'),
(30, 30, 'AJ1027', 5, 'Amazing quality and comfort. Design and colors on the cleats are really cool. Maximum comfort and quality for great price.', '2026-11-01 04:01:00'),
(31, 31, 'AA', 5, 'Great cleats for kids. We went to store and they had a variety of cleats. Great price and variety.', '2026-11-01 04:01:00'),
(32, 32, 'Lk29', 4, 'Affordable and good quality. These cleats are pretty nice. They look pretty good, and are decent quality.', '2026-11-01 04:01:00'),
(33, 33, 'Jonnymito', 5, 'Fantastic boots. Super comfortable and great value. Bought these for my son who is an occasional footballer.', '2026-11-01 04:01:00'),
(34, 34, 'AndyD', 5, 'Great first cleats, my son loves them. They are comfortable and look great. Color filter options made it easy to find.', '2026-11-01 04:01:00'),
(35, 35, '3Dev', 5, 'Amazing purchase. I would recommend this product to anyone interested in buying. It has got a great look and very comfortable.', '2026-11-01 04:01:00'),
(36, 36, 'Tanyarm11', 5, 'After we sized up - perfect. My son loves playing football in them! He finds them really comfortable.', '2026-11-01 04:01:00'),
(37, 37, '3Dev', 5, 'Very good purchase. I think for the price paid, it was worth it. It serves the purpose and my son says it\'s very convenient.', '2026-11-01 04:01:00'),
(38, 38, 'Chris80', 5, 'Brilliant purchase for my eldest boy. Strong boots. Good on the eye too, bit retro look too. Nice and comfortable.', '2026-11-01 04:01:00'),
(39, 39, 'Urko', 5, 'Good football boots, perfect fit, used so far on 2 occasions on grass in wet weather and not slipped.', '2026-11-01 04:01:00'),
(40, 40, 'TrM11', 5, 'Sized up as football boots but are perfect! My son\'s first pair of boots and he loves them! Look and fit great.', '2026-11-01 04:01:00'),
(41, 41, 'Eork11', 1, 'ไม่ผ่านQC QCเช็นผ่านมาได้ไง สินค้ามีตำหนิเยอะมาก ทั้งที่เป็นตัวลิมิเต็ด น่าจะทำออกมาได้ดีกว่านี้', '2026-11-01 04:01:00'),
(42, 42, 'sangwong', 5, 'ความยาวรองเท้าพอดี ความกว้างเล็กกว่าปกติ แต่ว่าสามารถสั่งไซต์ปกติที่เคยสั่งได้ หนังนุ่มกำลังดี', '2026-11-01 04:01:00'),
(43, 43, 'Ting', 5, 'รองเท้าเหมาะสำหรับเด็ก หนังหน้าเท้าไม่แข็งจนเกินไป ใส่สบาย กระชับเวลาเตะฟุตบอล', '2026-11-01 04:01:00'),
(44, 44, 'Phoo78', 5, 'ร้านจัดส่งไวมากครับ สินค้าสวยงามตามที่สั่ง ดีมากครับ', '2026-11-01 04:01:00'),
(45, 45, 'Chur', 4, 'ใส่สบายเท้า เหมาะกับคนเท้าบาน ได้มาตอนช่วงลดราคาถือว่าคุ้มค่าครับ', '2026-11-01 04:01:00'),
(46, 46, 'Bas55', 4, 'มาแล้วครับระยะเวลาส่งประมาณ5วัน สินค้าของแท้ครับราคาก็ดีครับ คุ้มค่ามาก', '2026-11-01 04:01:00'),
(47, 47, 'Kitsada', 5, 'ดีไซน์โฉบเฉี่ยว กระชับข้อเท้า ปุ่มสตั๊ดจิกหญ้าดีเยี่ยม คุ้มราคาสุดๆ', '2026-11-01 04:01:00'),
(48, 48, 'Supachok', 5, 'สปีดต้นดีมาก เบาเท้า เตะบอลพุ่งแรง คุ้มค่าสมราคา วิ่งเต็มสนามสบายๆ', '2026-11-01 04:01:00'),
(49, 49, 'Sarach', 5, 'คอนโทรลบอลนิ่งมาก วางบอลยาวได้แม่นยำ พื้น FG เกาะหญ้าธรรมชาติได้ดี', '2026-11-01 04:01:00'),
(50, 50, 'Theerathon', 5, 'เปิดบอลโค้งเข้าเป้าทุกลูก หนังนุ่ม ไม่กัดส้นเท้าเลยครับ สัมผัสคลาสสิก', '2026-11-01 04:01:00'),
(51, 51, 'Chanathip', 5, 'คล่องตัวสูง เลี้ยวเปลี่ยนทิศทางได้เร็วมาก น้ำหนักเบาสบายเท้า คอนโทรลง่าย', '2026-11-01 04:01:00'),
(52, 52, 'Teerasil', 5, 'ยิงประตูล็อคข้อเท้าดีมาก หนังแท้คลาสสิก นุ่มสบายที่สุด สไตล์ Copa แท้', '2026-11-01 04:01:00'),
(53, 53, 'Worachit', 4, 'ใส่สบาย เล่นได้ทั้งหญ้าจริงและหญ้าเทียม สีสันสะดุดตา สวมใส่ง่าย', '2026-11-01 04:01:00'),
(54, 54, 'Kritsada', 5, 'การจัดส่งรวดเร็ว สินค้าตรงปก สวมใส่พอดีตามตารางไซส์ ไม่ต้องเผื่อไซส์', '2026-11-01 04:01:00'),
(55, 55, 'Sasalak', 5, 'วิ่งไม่มีสะดุด ปุ่มสตั๊ดกระจายน้ำหนักได้ดี ไม่ปวดฝ่าเท้าเวลาวิ่งสปีด', '2026-11-01 04:01:00'),
(56, 56, 'Kawin', 5, 'เกาะสนามแน่นมาก เซฟจังหวะพุ่งตัวได้มั่นใจ 100% สตั๊ดคุณภาพระดับโลก', '2026-11-01 04:01:00');

