import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import productService from '../../services/productService';
import categoryService from '../../services/categoryService';
import brandService from '../../services/brandService';

const ProductForm = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;

  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);
  const [imagePreview, setImagePreview] = useState(null);

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    price: '',
    sale_price: '',
    stock_quantity: '',
    category_id: '',
    brand_id: '',
    is_active: true,
    image: null,
  });

  useEffect(() => {
    fetchCategoriesAndBrands();
    if (isEdit) {
      fetchProduct();
    }
  }, [id]);

  const fetchCategoriesAndBrands = async () => {
    try {
      const [categoriesRes, brandsRes] = await Promise.all([
        categoryService.getCategories(),
        brandService.getBrands(),
      ]);
      setCategories(categoriesRes.data || []);
      setBrands(brandsRes.data || []);
    } catch (error) {
      console.error('Error fetching categories/brands:', error);
    }
  };

  const fetchProduct = async () => {
    try {
      setLoading(true);
      const response = await productService.getProductById(id);
      const product = response.data;

      setFormData({
        name: product.name || '',
        description: product.description || '',
        price: product.price || '',
        sale_price: product.sale_price || '',
        stock_quantity: product.stock_quantity || '',
        category_id: product.category_id || '',
        brand_id: product.brand_id || '',
        is_active: product.is_active ?? true,
        image: null,
      });

      if (product.image_url) {
        setImagePreview(product.image_url);
      }
    } catch (error) {
      console.error('Error fetching product:', error);
      alert('Không thể tải thông tin sản phẩm');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setFormData((prev) => ({ ...prev, image: file }));
      setImagePreview(URL.createObjectURL(file));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      setLoading(true);

      const submitData = new FormData();
      submitData.append('name', formData.name);
      submitData.append('description', formData.description);
      submitData.append('price', formData.price);
      submitData.append('stock_quantity', formData.stock_quantity);
      submitData.append('category_id', formData.category_id);
      submitData.append('brand_id', formData.brand_id);
      submitData.append('is_active', formData.is_active ? 1 : 0);

      if (formData.sale_price) {
        submitData.append('sale_price', formData.sale_price);
      }

      if (formData.image) {
        submitData.append('image', formData.image);
      }

      if (isEdit) {
        submitData.append('_method', 'PUT');
        await productService.updateProduct(id, submitData);
        alert('Cập nhật sản phẩm thành công!');
      } else {
        await productService.createProduct(submitData);
        alert('Tạo sản phẩm thành công!');
      }

      navigate('/admin/products');
    } catch (error) {
      console.error('Error saving product:', error);
      alert(
        `Không thể ${isEdit ? 'cập nhật' : 'tạo'} sản phẩm: ${
          error.response?.data?.message || error.message
        }`
      );
    } finally {
      setLoading(false);
    }
  };

  if (loading && isEdit) {
    return <div className="loading">Đang tải...</div>;
  }

  return (
    <div className="admin-form-container">
      <div className="admin-header">
        <h1>{isEdit ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới'}</h1>
        <button onClick={() => navigate('/admin/products')} className="btn-back">
          Quay lại
        </button>
      </div>

      <form onSubmit={handleSubmit} className="admin-form">
        <div className="form-row">
          <div className="form-group">
            <label htmlFor="name">
              Tên sản phẩm <span className="required">*</span>
            </label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
              className="form-control"
            />
          </div>

          <div className="form-group">
            <label htmlFor="category_id">
              Danh mục <span className="required">*</span>
            </label>
            <select
              id="category_id"
              name="category_id"
              value={formData.category_id}
              onChange={handleChange}
              required
              className="form-control"
            >
              <option value="">Chọn danh mục</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.name}
                </option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <label htmlFor="brand_id">
              Thương hiệu <span className="required">*</span>
            </label>
            <select
              id="brand_id"
              name="brand_id"
              value={formData.brand_id}
              onChange={handleChange}
              required
              className="form-control"
            >
              <option value="">Chọn thương hiệu</option>
              {brands.map((brand) => (
                <option key={brand.id} value={brand.id}>
                  {brand.name}
                </option>
              ))}
            </select>
          </div>
        </div>

        <div className="form-group">
          <label htmlFor="description">Mô tả</label>
          <textarea
            id="description"
            name="description"
            value={formData.description}
            onChange={handleChange}
            rows="4"
            className="form-control"
          />
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="price">
              Giá gốc (VNĐ) <span className="required">*</span>
            </label>
            <input
              type="number"
              id="price"
              name="price"
              value={formData.price}
              onChange={handleChange}
              required
              min="0"
              step="1000"
              className="form-control"
            />
          </div>

          <div className="form-group">
            <label htmlFor="sale_price">Giá khuyến mãi (VNĐ)</label>
            <input
              type="number"
              id="sale_price"
              name="sale_price"
              value={formData.sale_price}
              onChange={handleChange}
              min="0"
              step="1000"
              className="form-control"
            />
          </div>

          <div className="form-group">
            <label htmlFor="stock_quantity">
              Số lượng tồn kho <span className="required">*</span>
            </label>
            <input
              type="number"
              id="stock_quantity"
              name="stock_quantity"
              value={formData.stock_quantity}
              onChange={handleChange}
              required
              min="0"
              className="form-control"
            />
          </div>
        </div>

        <div className="form-group">
          <label htmlFor="image">Hình ảnh</label>
          <input
            type="file"
            id="image"
            name="image"
            onChange={handleImageChange}
            accept="image/*"
            className="form-control"
          />
          {imagePreview && (
            <div className="image-preview">
              <img src={imagePreview} alt="Preview" />
            </div>
          )}
        </div>

        <div className="form-group">
          <label className="checkbox-label">
            <input
              type="checkbox"
              name="is_active"
              checked={formData.is_active}
              onChange={handleChange}
            />
            <span>Kích hoạt sản phẩm</span>
          </label>
        </div>

        <div className="form-actions">
          <button
            type="button"
            onClick={() => navigate('/admin/products')}
            className="btn-secondary"
          >
            Hủy
          </button>
          <button type="submit" disabled={loading} className="btn-primary">
            {loading ? 'Đang xử lý...' : isEdit ? 'Cập nhật' : 'Tạo mới'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ProductForm;
