class Product {
  final int id;
  final String name;
  final String imageUrl;
  final double price;
  final int seriesId;
  final String seriesName;
  final String groundType;
  final String description;
  bool isFavorite;

  Product({
    required this.id,
    required this.name,
    required this.imageUrl,
    required this.price,
    required this.seriesId,
    required this.seriesName,
    required this.groundType,
    required this.description,
    this.isFavorite = false,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    final id = int.tryParse('${json['id'] ?? json['product_id'] ?? 0}') ?? 0;

    final name = json['name']?.toString() ??
        json['product_name']?.toString() ??
        'Soccer Cleat';

    final seriesId =
        int.tryParse('${json['series_id'] ?? json['seriesId'] ?? 0}') ?? 0;

    String sName = json['series_name']?.toString() ??
        json['seriesName']?.toString() ??
        '';
    if (sName.isEmpty) {
      if (seriesId == 1) {
        sName = 'Predator';
      } else if (seriesId == 2) {
        sName = 'F50';
      } else if (seriesId == 3) {
        sName = 'Copa';
      } else if (name.toLowerCase().contains('f50')) {
        sName = 'F50';
      } else if (name.toLowerCase().contains('copa')) {
        sName = 'Copa';
      } else {
        sName = 'Predator';
      }
    }

    final imageUrl = (json['imageUrl'] ?? json['image_url'] ?? '').toString();

    final price = double.tryParse('${json['price'] ?? 0}') ?? 0.0;

    final groundType = json['ground_type']?.toString() ??
        json['groundType']?.toString() ??
        'FG';

    final description = json['description']?.toString() ??
        json['desc']?.toString() ??
        '';

    final isFavorite = json['is_favorite'] == true ||
        json['isFavorite'] == true ||
        json['is_favorite'] == 1;

    return Product(
      id: id,
      name: name,
      imageUrl: imageUrl,
      price: price,
      seriesId: seriesId,
      seriesName: sName,
      groundType: groundType,
      description: description,
      isFavorite: isFavorite,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'imageUrl': imageUrl,
      'price': price,
      'series_id': seriesId,
      'series_name': seriesName,
      'ground_type': groundType,
      'description': description,
      'is_favorite': isFavorite,
    };
  }
}
