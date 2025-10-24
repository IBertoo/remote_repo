-- ============================================
-- SCRIPT CORREGIDO Y ORDENADO PARA POSTGRESQL
-- ============================================

-- 1️⃣ Crear la base de datos (opcional)
DROP DATABASE IF EXISTS catalogo;
CREATE DATABASE catalogo
    WITH 
    ENCODING = 'UTF8'
    LC_COLLATE = 'C'
    LC_CTYPE = 'C'
    TEMPLATE = template0;

\connect catalogo

-- 2️⃣ Tipos personalizados
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_method') THEN
        CREATE TYPE payment_method AS ENUM ('cash_on_delivery');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'order_status') THEN
        CREATE TYPE order_status AS ENUM ('pending', 'completed');
    END IF;
END$$;

-- 3️⃣ Tabla: categories
DROP TABLE IF EXISTS categories CASCADE;
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO categories (id, name) VALUES
(1, 'tragos'),
(2, 'refresco'),
(3, 'ropa'),
(4, 'Linterna'),
(5, 'ferreteria'),
(6, 'pilas'),
(7, 'pilas_recargables'),
(9, 'pilaAlcalina'),
(10, 'linternarecargable'),
(11, 'candado');

-- 4️⃣ Tabla: orders
DROP TABLE IF EXISTS orders CASCADE;
CREATE TABLE orders (
    id UUID PRIMARY KEY,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total NUMERIC(10,2) NOT NULL,
    payment_method payment_method NOT NULL DEFAULT 'cash_on_delivery',
    status order_status NOT NULL DEFAULT 'pending'
);

COMMENT ON COLUMN orders.id IS 'Unique order ID (UUID)';
COMMENT ON COLUMN orders.created_at IS 'Order creation date';
COMMENT ON COLUMN orders.total IS 'Total order amount';
COMMENT ON COLUMN orders.payment_method IS 'Payment method';
COMMENT ON COLUMN orders.status IS 'Order status';

-- 5️⃣ Tabla: products
DROP TABLE IF EXISTS products CASCADE;
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10,2) NOT NULL,
    category_id INTEGER,
    marca VARCHAR(100) NOT NULL DEFAULT '',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tags VARCHAR(255) NOT NULL DEFAULT '',
    descripcion_corta VARCHAR(255) NOT NULL DEFAULT '',
    visitas INTEGER NOT NULL DEFAULT 0,
    CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

COMMENT ON COLUMN products.marca IS 'Marca del producto';
COMMENT ON COLUMN products.fecha_creacion IS 'Fecha de creación del producto';
COMMENT ON COLUMN products.fecha_actualizacion IS 'Fecha de última actualización';
COMMENT ON COLUMN products.tags IS 'Etiquetas del producto, separadas por comas';
COMMENT ON COLUMN products.descripcion_corta IS 'Descripción breve del producto';
COMMENT ON COLUMN products.visitas IS 'Número de visitas al producto';

-- 6️⃣ Insertar productos (solo IDs válidos de categorías)
INSERT INTO products (id, name, description, price, category_id, marca, fecha_creacion, fecha_actualizacion, tags, descripcion_corta, visitas) VALUES
(1, 'Camiseta Deportiva', 'Camiseta transpirable ideal para deportes.', 24.99, 3, 'Nike', '2025-09-11 09:04:04', '2025-09-11 10:15:27', 'ropa,deporte', 'Camiseta deportiva ligera', 1),
(2, 'Pantalones Chinos', 'Pantalones casuales de corte slim.', 39.99, 3, 'Zara', '2025-09-11 09:04:04', '2025-09-11 09:04:04', 'ropa,casual', 'Pantalón casual de algodón', 0),
(57, 'Candado Amarillo Lions 38mm', 'Acabado niquelado dorado en cuerpo de 38mm, arco grueso.', 50.00, 11, 'Lions', '2025-10-07 20:42:39', '2025-10-17 03:48:54', 'candado,unidad', 'Candado de latón con acabado dorado', 0),
(58, 'Candado Padlock Xwang 20 25 32', 'Versión colorida del clásico, cuerpo de latón 45mm.', 45.00, 11, 'Padlock', '2025-10-07 21:17:59', '2025-10-17 03:46:53', 'candado,docena', 'Candado de latón resistente', 1),
(59, 'Linterna Recargable Ewtto ET-F5964', 'Linterna recargable con batería incluida.', 50.00, 10, 'Ewtto', '2025-10-09 03:27:39', '2025-10-17 03:40:19', 'linterna,recargable', 'Linterna y lámpara lateral Ewtto.', 11);

-- 7️⃣ Tabla: product_images
DROP TABLE IF EXISTS product_images CASCADE;
CREATE TABLE product_images (
    id SERIAL PRIMARY KEY,
    product_id INTEGER NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insertar imágenes
INSERT INTO product_images (id, product_id, image_url) VALUES
(1, 1, '/uploads/default.webp'),
(2, 2, '/uploads/default.webp'),
(156, 57, '/uploads/img_68f1bcc09040a.jpg');

-- 8️⃣ Tabla: order_items
DROP TABLE IF EXISTS order_items CASCADE;
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id UUID NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price NUMERIC(10,2) NOT NULL,
    CONSTRAINT fk_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_product_order FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

COMMENT ON COLUMN order_items.order_id IS 'Reference to orders.id';
COMMENT ON COLUMN order_items.product_id IS 'Reference to products.id';
COMMENT ON COLUMN order_items.quantity IS 'Quantity of product';
COMMENT ON COLUMN order_items.price IS 'Price at the time of order';

-- 9️⃣ Tabla: users
DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_username UNIQUE (username)
);

INSERT INTO users (id, username, password_hash, created_at) VALUES
(2, 'erlan', '$2y$10$TXNLyQj9hkN0lEQPZFpD9uwUr4GVtLSm.Uf52XVVIr/22mkT99ATe', '2025-10-17 00:46:02'),
(4, 'admin', '$2y$10$hEjCqA7OLoBsfJOaTD3X6e973iQS1sj.vwyTv3BOd4P2hooRSk56.', '2025-10-17 00:56:24');

-- 🔟 Índices
CREATE INDEX idx_order_items_order_id ON order_items (order_id);
CREATE INDEX idx_order_items_product_id ON order_items (product_id);
CREATE INDEX idx_product_images_product_id ON product_images (product_id);

-- ✅ Fin del script
