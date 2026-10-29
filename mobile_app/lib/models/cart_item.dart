import 'product.dart';

class CartItem {
  final Product product;
  int quantity;
  String size;

  CartItem({
    required this.product,
    this.quantity = 1,
    this.size = 'US 9',
  });

  double get totalPrice => product.price * quantity;
}
