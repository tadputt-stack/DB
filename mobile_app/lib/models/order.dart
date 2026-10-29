class OrderItemModel {
  final int productId;
  final String productName;
  final String size;
  final int quantity;
  final double price;
  final double subtotal;
  final String? imageUrl;

  OrderItemModel({
    required this.productId,
    required this.productName,
    this.size = 'US 9',
    required this.quantity,
    required this.price,
    required this.subtotal,
    this.imageUrl,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    final rawProdId =
        json['product_id'] ?? json['productId'] ?? json['id'] ?? 0;
    final prodId = rawProdId is num
        ? rawProdId.toInt()
        : int.tryParse(rawProdId.toString()) ?? 0;

    final rawQty = json['quantity'] ?? json['qty'] ?? 1;
    final qty =
        rawQty is num ? rawQty.toInt() : int.tryParse(rawQty.toString()) ?? 1;

    double price = 0.0;
    final rawPrice = json['price'];
    if (rawPrice is num) {
      price = rawPrice.toDouble();
    } else if (rawPrice != null) {
      price = double.tryParse(rawPrice.toString()) ?? 0.0;
    }

    double subtotal = price * qty;
    final rawSubtotal = json['subtotal'];
    if (rawSubtotal is num) {
      subtotal = rawSubtotal.toDouble();
    } else if (rawSubtotal != null) {
      subtotal = double.tryParse(rawSubtotal.toString()) ?? (price * qty);
    }

    final rawImg =
        json['imageUrl'] ?? json['image_url'] ?? json['image'] ?? json['img'];

    return OrderItemModel(
      productId: prodId,
      productName: json['product_name']?.toString() ??
          json['name']?.toString() ??
          'Soccer Cleat',
      size: json['size']?.toString() ?? 'US 9',
      quantity: qty,
      price: price,
      subtotal: subtotal,
      imageUrl: rawImg?.toString(),
    );
  }

  Map<String, dynamic> toApiJson() {
    return {
      'product_id': productId,
      'product_name': productName,
      'size': size,
      'quantity': quantity,
      'qty': quantity,
      'price': price,
      'subtotal': subtotal,
      if (imageUrl != null) 'imageUrl': imageUrl,
    };
  }
}

class OrderModel {
  final int? orderId;
  final int? userId;
  final String customerName;
  final String customerEmail;
  final double totalPrice;
  final String status;
  final DateTime createdAt;
  final List<OrderItemModel> items;

  OrderModel({
    this.orderId,
    this.userId = 1,
    required this.customerName,
    required this.customerEmail,
    required this.totalPrice,
    this.status = 'สำเร็จ',
    required this.createdAt,
    required this.items,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    final rawItems = json['items'] ?? json['order_items'] ?? json['orderItems'];
    List<OrderItemModel> itemsList = [];
    if (rawItems is List) {
      for (final i in rawItems) {
        if (i is Map<String, dynamic>) {
          itemsList.add(OrderItemModel.fromJson(i));
        } else if (i is Map) {
          itemsList.add(OrderItemModel.fromJson(Map<String, dynamic>.from(i)));
        }
      }
    }

    final rawOrderId = json['order_id'] ?? json['orderId'] ?? json['id'];
    final orderId = rawOrderId != null
        ? (rawOrderId is num
            ? rawOrderId.toInt()
            : int.tryParse(rawOrderId.toString()))
        : null;

    final rawUserId = json['user_id'] ?? json['userId'];
    final userId = rawUserId != null
        ? (rawUserId is num
            ? rawUserId.toInt()
            : int.tryParse(rawUserId.toString()))
        : null;

    double totalPrice = 0.0;
    final rawPrice = json['total_price'] ?? json['totalPrice'] ?? json['price'];
    if (rawPrice is num) {
      totalPrice = rawPrice.toDouble();
    } else if (rawPrice != null) {
      totalPrice = double.tryParse(rawPrice.toString()) ?? 0.0;
    }

    DateTime orderDate = DateTime.now();
    final rawDate =
        json['created_at'] ?? json['createdAt'] ?? json['date'] ?? json['order_date'];
    if (rawDate != null) {
      final dateStr = rawDate.toString().replaceAll(' ', 'T');
      orderDate = DateTime.tryParse(dateStr) ??
          DateTime.tryParse(rawDate.toString()) ??
          DateTime.now();
    }

    return OrderModel(
      orderId: orderId,
      userId: userId,
      customerName: json['customer_name']?.toString() ??
          json['customerName']?.toString() ??
          'User Customer',
      customerEmail: json['customer_email']?.toString() ??
          json['customerEmail']?.toString() ??
          'user@example.com',
      totalPrice: totalPrice,
      status: json['status']?.toString() ?? 'สำเร็จ',
      createdAt: orderDate,
      items: itemsList,
    );
  }

  Map<String, dynamic> toApiJson() {
    return {
      if (userId != null) 'user_id': userId,
      'customer_name': customerName,
      'customer_email': customerEmail,
      'total_price': totalPrice,
      'status': status,
      'items': items.map((item) => item.toApiJson()).toList(),
    };
  }
}
