import 'dart:convert';
import 'package:flutter/foundation.dart' show kIsWeb, debugPrint;
import 'package:http/http.dart' as http;
import '../models/product.dart';
import '../models/order.dart';
import '../models/product_review.dart';

class ApiService {
  static String get baseUrl =>
      kIsWeb ? 'http://localhost:8000' : 'http://10.0.2.2:8000';

  static Future<List<Product>> fetchProducts() async {
    final primaryUrl = '$baseUrl/products';
    final fallbackPhpUrl = '$baseUrl/api_products.php';
    debugPrint('--> กำลังดึงสินค้าจาก: $baseUrl/products');

    http.Response? response;
    try {
      response = await http
          .get(Uri.parse(primaryUrl))
          .timeout(const Duration(seconds: 8));

      debugPrint('--> HTTP STATUS: ${response.statusCode}');
      debugPrint('--> BODY LENGTH: ${response.body.length}');

      if (response.statusCode == 404) {
        debugPrint('[ApiService] หน้า /products คืน 404 สลับไปยิงตรงที่: $fallbackPhpUrl');
        response = await http
            .get(Uri.parse(fallbackPhpUrl))
            .timeout(const Duration(seconds: 8));
        debugPrint('--> STATUS: ${response.statusCode}');
        debugPrint('--> BODY LENGTH: ${response.body.length}');
      }
    } catch (e, stackTrace) {
      debugPrint('[ApiService] เกิดข้อยกเว้นขณะดึงสินค้าจาก $primaryUrl: $e');
      debugPrint('[ApiService] Stack trace: $stackTrace');
      try {
        debugPrint('[ApiService] ลองพยายามยิงไปที่ fallback endpoint: $fallbackPhpUrl');
        response = await http
            .get(Uri.parse(fallbackPhpUrl))
            .timeout(const Duration(seconds: 8));
        debugPrint('--> STATUS: ${response.statusCode}');
        debugPrint('--> BODY LENGTH: ${response.body.length}');
      } catch (fallbackError) {
        debugPrint('[ApiService] Fallback ก็ไม่รอด: $fallbackError');
        throw Exception(
            'ต่อเซิร์ฟเวอร์ไม่ได้เลยจ้าที่ $baseUrl: $e (Fallback error: $fallbackError)');
      }
    }

    if (response == null || response.statusCode != 200) {
      final errorMsg =
          '[ApiService] HTTP error ดึงสินค้าไม่สำเร็จ รหัสสถานะ: ${response?.statusCode}, ข้อมูล: ${response?.body}';
      debugPrint(errorMsg);
      throw Exception(errorMsg);
    }

    try {
      final body = utf8.decode(response.bodyBytes);
      final dynamic decoded = jsonDecode(body);
      if (decoded is List) {
        final List<Product> products = [];
        for (var i = 0; i < decoded.length; i++) {
          final item = decoded[i];
          try {
            if (item is Map<String, dynamic>) {
              products.add(Product.fromJson(item));
            } else if (item is Map) {
              products.add(Product.fromJson(Map<String, dynamic>.from(item)));
            }
          } catch (itemErr, itemStack) {
            debugPrint('[ApiService] เตือน: แปลงสินค้าตัวที่ $i ไม่ผ่าน: $itemErr');
            debugPrint('[ApiService] ข้อมูลสินค้าตัวนั้น: $item');
            debugPrint('[ApiService] Stack trace: $itemStack');
          }
        }
        debugPrint('[ApiService] โหลดสินค้าสำเร็จ ${products.length} รายการจาก MySQL');
        return products;
      } else {
        throw FormatException('คาดหวังได้ JSON Array ของสินค้า แต่ดันได้: ${decoded.runtimeType}');
      }
    } catch (e, stackTrace) {
      debugPrint('--> เกิดข้อผิดพลาดตอนแกะ JSON: $e');
      debugPrint('--> STACK TRACE: $stackTrace');
      debugPrint('[ApiService] ข้อมูลดิบที่ได้มาคือ: ${response.body}');
      throw Exception('แปลง JSON สินค้าไม่สำเร็จ: $e');
    }
  }

  static Future<List<OrderModel>> fetchOrders() async {
    final primaryUrl = '$baseUrl/orders';
    final fallbackPhpUrl = '$baseUrl/api_orders.php';
    debugPrint('[ApiService] กำลังดึงประวัติออเดอร์จาก: $primaryUrl');

    http.Response? response;
    try {
      response = await http
          .get(Uri.parse(primaryUrl))
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 404) {
        debugPrint('[ApiService] หน้า /orders คืน 404 สลับไปยิงตรงที่: $fallbackPhpUrl');
        response = await http
            .get(Uri.parse(fallbackPhpUrl))
            .timeout(const Duration(seconds: 8));
      }
    } catch (e, stackTrace) {
      debugPrint('[ApiService] ยิงขอประวัติออเดอร์ไม่สำเร็จ: $e');
      debugPrint('[ApiService] Stack trace: $stackTrace');
      try {
        response = await http
            .get(Uri.parse(fallbackPhpUrl))
            .timeout(const Duration(seconds: 8));
      } catch (fallbackError) {
        debugPrint('[ApiService] Fallback ออเดอร์ก็พัง: $fallbackError');
        throw Exception('ต่อเซิร์ฟเวอร์เพื่อดูออเดอร์ไม่ได้จ้า: $e');
      }
    }

    if (response == null || response.statusCode != 200) {
      final errorMsg =
          '[ApiService] HTTP error ดึงออเดอร์ไม่ได้ รหัส: ${response?.statusCode}, ข้อมูล: ${response?.body}';
      debugPrint(errorMsg);
      throw Exception(errorMsg);
    }

    try {
      final body = utf8.decode(response.bodyBytes);
      final dynamic decoded = jsonDecode(body);
      if (decoded is List) {
        final List<OrderModel> orders = [];
        for (var i = 0; i < decoded.length; i++) {
          final item = decoded[i];
          try {
            if (item is Map<String, dynamic>) {
              orders.add(OrderModel.fromJson(item));
            } else if (item is Map) {
              orders.add(OrderModel.fromJson(Map<String, dynamic>.from(item)));
            }
          } catch (itemErr, itemStack) {
            debugPrint('[ApiService] แปลงออเดอร์ที่ $i ไม่ผ่าน: $itemErr');
            debugPrint('[ApiService] ข้อมูล: $item');
            debugPrint('[ApiService] Stack trace: $itemStack');
          }
        }
        debugPrint('[ApiService] โหลดออเดอร์สำเร็จทั้งหมด ${orders.length} รายการ');
        return orders;
      } else {
        throw FormatException('คาดหวัง JSON Array ของออเดอร์ แต่ได้: ${decoded.runtimeType}');
      }
    } catch (e, stackTrace) {
      debugPrint('[ApiService] แกะ JSON ออเดอร์ไม่ผ่าน: $e');
      debugPrint('[ApiService] Raw response: ${response.body}');
      debugPrint('[ApiService] Stack trace: $stackTrace');
      throw Exception('แปลง JSON คำสั่งซื้อไม่สำเร็จ: $e');
    }
  }

  static Future<bool> createOrder(OrderModel order) async {
    final primaryUrl = '$baseUrl/orders';
    final fallbackPhpUrl = '$baseUrl/api_orders.php';
    debugPrint('[ApiService] ยิงออเดอร์ใหม่ไปที่: $primaryUrl');

    final payload = order.toApiJson();
    final jsonString = jsonEncode(payload);
    debugPrint('[ApiService] ก้อนข้อมูล JSON ที่ส่งไป: $jsonString');

    http.Response? response;
    try {
      response = await http
          .post(
            Uri.parse(primaryUrl),
            headers: {
              'Content-Type': 'application/json; charset=utf-8',
              'Accept': 'application/json',
            },
            body: jsonString,
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 404) {
        debugPrint('[ApiService] Primary POST /orders คืน 404 สลับไปยิงที่: $fallbackPhpUrl');
        response = await http
            .post(
              Uri.parse(fallbackPhpUrl),
              headers: {
                'Content-Type': 'application/json; charset=utf-8',
                'Accept': 'application/json',
              },
              body: jsonString,
            )
            .timeout(const Duration(seconds: 8));
      }
    } catch (e, stackTrace) {
      debugPrint('[ApiService] เกิดข้อผิดพลาดขณะยิงสั่งซื้อสินค้า: $e');
      debugPrint('[ApiService] Stack trace: $stackTrace');
      try {
        response = await http
            .post(
              Uri.parse(fallbackPhpUrl),
              headers: {
                'Content-Type': 'application/json; charset=utf-8',
                'Accept': 'application/json',
              },
              body: jsonString,
            )
            .timeout(const Duration(seconds: 8));
      } catch (fallbackError) {
        debugPrint('[ApiService] Fallback ยิงสั่งซื้อไม่ผ่าน: $fallbackError');
        return false;
      }
    }

    debugPrint('[ApiService] ผลลัพธ์สถานะสั่งซื้อ: ${response?.statusCode}');
    debugPrint('[ApiService] ข้อความตอบกลับ: ${response?.body}');

    if (response != null && (response.statusCode == 200 || response.statusCode == 201)) {
      debugPrint('[ApiService] บันทึกออเดอร์ลง MySQL เรียบร้อยแล้วจ้า!');
      return true;
    } else {
      debugPrint(
          '[ApiService] บันทึกออเดอร์ไม่ลงจ้า สถานะ: ${response?.statusCode}, ข้อความ: ${response?.body}');
      return false;
    }
  }

  static Future<List<ProductReview>> fetchReviews({int? productId}) async {
    final queryStr = productId != null && productId > 0 ? '?product_id=$productId' : '';
    final primaryUrl = '$baseUrl/ratings$queryStr';
    final fallbackPhpUrl = '$baseUrl/api_ratings.php$queryStr';
    debugPrint('[ApiService] กำลังดึงรีวิวสินค้าจาก: $primaryUrl');

    http.Response? response;
    try {
      response = await http.get(Uri.parse(primaryUrl)).timeout(const Duration(seconds: 8));
      if (response.statusCode == 404) {
        debugPrint('[ApiService] /ratings คืน 404 สลับไปยิงที่: $fallbackPhpUrl');
        response = await http.get(Uri.parse(fallbackPhpUrl)).timeout(const Duration(seconds: 8));
      }
    } catch (e) {
      debugPrint('[ApiService] เกิดข้อผิดพลาดขณะดึงรีวิวจาก primary: $e');
      try {
        response = await http.get(Uri.parse(fallbackPhpUrl)).timeout(const Duration(seconds: 8));
      } catch (fallbackError) {
        debugPrint('[ApiService] Fallback รีวิวก็ไม่สำเร็จ: $fallbackError');
      }
    }

    if (response != null && response.statusCode == 200) {
      try {
        final body = utf8.decode(response.bodyBytes);
        final dynamic decoded = jsonDecode(body);
        if (decoded is List) {
          final List<ProductReview> reviews = [];
          for (final item in decoded) {
            if (item is Map<String, dynamic>) {
              reviews.add(ProductReview.fromJson(item));
            } else if (item is Map) {
              reviews.add(ProductReview.fromJson(Map<String, dynamic>.from(item)));
            }
          }
          debugPrint('[ApiService] โหลดรีวิวสำเร็จ ${reviews.length} รายการ');
          return reviews;
        }
      } catch (e) {
        debugPrint('[ApiService] แปลง JSON รีวิวไม่สำเร็จ: $e');
      }
    }

    // Fallback seed reviews if backend cannot be reached
    return _getFallbackReviews(productId);
  }

  static Future<bool> submitReview({
    required int productId,
    required String reviewerName,
    required int score,
    required String comment,
  }) async {
    final primaryUrl = '$baseUrl/ratings';
    final fallbackPhpUrl = '$baseUrl/api_ratings.php';
    final payload = jsonEncode({
      'product_id': productId,
      'reviewer_name': reviewerName.trim().isEmpty ? 'ผู้ใช้งาน OnlySport' : reviewerName.trim(),
      'score': score,
      'comment': comment.trim(),
    });

    http.Response? response;
    try {
      response = await http.post(
        Uri.parse(primaryUrl),
        headers: {'Content-Type': 'application/json; charset=utf-8'},
        body: payload,
      ).timeout(const Duration(seconds: 8));

      if (response.statusCode == 404) {
        response = await http.post(
          Uri.parse(fallbackPhpUrl),
          headers: {'Content-Type': 'application/json; charset=utf-8'},
          body: payload,
        ).timeout(const Duration(seconds: 8));
      }
    } catch (e) {
      debugPrint('[ApiService] Error submitting review: $e');
      try {
        response = await http.post(
          Uri.parse(fallbackPhpUrl),
          headers: {'Content-Type': 'application/json; charset=utf-8'},
          body: payload,
        ).timeout(const Duration(seconds: 8));
      } catch (err2) {
        debugPrint('[ApiService] Fallback submit review error: $err2');
        return false;
      }
    }

    return response != null && (response.statusCode == 200 || response.statusCode == 201);
  }

  static List<ProductReview> _getFallbackReviews(int? productId) {
    final allSeeds = [
      ProductReview(
        ratingId: 1,
        productId: 1,
        productName: 'Adidas Predator Elite FT FG',
        reviewerName: 'Chanathip',
        score: 5,
        comment: 'คล่องตัวสูง เลี้ยวเปลี่ยนทิศทางได้เร็วมาก น้ำหนักเบาสบายเท้า คอนโทรลง่าย',
        createdAt: '2026-11-01 04:01:00',
      ),
      ProductReview(
        ratingId: 2,
        productId: 2,
        productName: 'Adidas Copa Pure 3 League FG/MG',
        reviewerName: 'Teerasil',
        score: 5,
        comment: 'ยิงประตูล็อคข้อเท้าดีมาก หนังแท้คลาสสิก นุ่มสบายที่สุด สไตล์ Copa แท้',
        createdAt: '2026-11-01 04:01:00',
      ),
      ProductReview(
        ratingId: 3,
        productId: 3,
        productName: 'Adidas F50 Elite FG',
        reviewerName: 'Theerathon',
        score: 5,
        comment: 'เปิดบอลโค้งเข้าเป้าทุกลูก หนังนุ่ม ไม่กัดส้นเท้าเลยครับ สัมผัสคลาสสิก',
        createdAt: '2026-11-01 04:01:00',
      ),
      ProductReview(
        ratingId: 4,
        productId: 4,
        productName: 'Adidas Predator 24 Pro FG',
        reviewerName: 'Supachok',
        score: 5,
        comment: 'สปีดต้นดีมาก เบาเท้า เตะบอลพุ่งแรง คุ้มค่าสมราคา วิ่งเต็มสนามสบายๆ',
        createdAt: '2026-11-01 04:01:00',
      ),
      ProductReview(
        ratingId: 5,
        productId: 5,
        productName: 'Adidas Copa Gloro FG',
        reviewerName: 'Sarach',
        score: 5,
        comment: 'คอนโทรลบอลนิ่งมาก วางบอลยาวได้แม่นยำ พื้น FG เกาะหญ้าธรรมชาติได้ดี',
        createdAt: '2026-11-01 04:01:00',
      ),
      ProductReview(
        ratingId: 6,
        productId: 6,
        productName: 'Adidas F50 League LL FG/MG',
        reviewerName: 'Mandy25',
        score: 5,
        comment: 'Great all rounder football boots. Great colours and very comfortable at a great price.',
        createdAt: '2026-11-01 04:01:00',
      ),
      ProductReview(
        ratingId: 7,
        productId: 7,
        productName: 'Adidas Predator Club TF',
        reviewerName: 'Alex',
        score: 5,
        comment: 'Super comfy, son loves them. Great value for money.',
        createdAt: '2026-11-01 04:01:00',
      ),
      ProductReview(
        ratingId: 8,
        productId: 8,
        productName: 'Adidas Copa Pure 2 Club IN',
        reviewerName: 'CopaKing',
        score: 5,
        comment: 'Best youth cleats. Supple so don\'t take forever to break in. These Copas are perfect.',
        createdAt: '2026-11-01 04:01:00',
      ),
    ];

    if (productId != null && productId > 0) {
      final matched = allSeeds.where((r) => r.productId == productId).toList();
      return matched.isNotEmpty ? matched : allSeeds.take(2).toList();
    }
    return allSeeds;
  }
}
