import { useState, useEffect } from 'react';
import brandService from '../../services/brandService';

const Brands = () => {
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [imagePreview, setImagePreview] = useState(null);

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    image: null,
  });

  useEffect(() => {
    fetchBrands();
  }, []);

  const fetchBrands = async () => {
    try {
      setLoading(true);
      const response = await brandService.getBrands();
      setBrands(response.data || []);
    } catch (error) {
      console.error('Error fetching brands:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
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
      const submitData = new FormData();
      submitData.append('name', formData.name);
      submitData.append('description', formData.description || '');
      if (formData.image) {
        submitData.append('image', formData.image);
      }

      if (editingId) {
        submitData.append('_method', 'PUT');
        await brandService.updateBrand(editingId, submitData);
        alert('Cập nhật thương hiệu thành công!');
      } else {
        await brandService.createBrand(submitData);
        alert('Tạo thương hiệu thành công!');
      }

      resetForm();
      fetchBrands();
    } catch (error) {
      console.error('Error saving brand:', error);
      alert(
        `Không thể ${editingId ? 'cập nhật' : 'tạo'} thương hiệu: ${
          error.response?.data?.message || error.message
        }`
      );
    }
  };

  const handleEdit = (brand) => {
    setEditingId(brand.id);
    setFormData({
      name: brand.name,
      description: brand.description || '',
      image: null,
    });
    setImagePreview(brand.image_url);
    setShowForm(true);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Bạn có chắc muốn xóa thương hiệu này?')) {
      return;
    }

    try {
      await brandService.deleteBrand(id);
      fetchBrands();
      alert('Xóa thương hiệu thành công!');
    } catch (error) {
      console.error('Error deleting brand:', error);
      alert('Không thể xóa thương hiệu');
    }
  };

  const resetForm = () => {
    setFormData({ name: '', description: '', image: null });
    setImagePreview(null);
    setEditingId(null);
    setShowForm(false);
  };

  if (loading) {
    return <div className="loading">Đang tải...</div>;
  }

  return (
    <div className="admin-brands">
      <div className="admin-header">
        <h1>Quản Lý Thương Hiệu</h1>
        <button
          onClick={() => {
            resetForm();
            setShowForm(true);
          }}
          className="btn-primary"
        >
          Thêm Thương Hiệu Mới
        </button>
      </div>

      {showForm && (
        <div className="admin-form-card">
          <div className="form-header">
            <h2>{editingId ? 'Sửa Thương Hiệu' : 'Thêm Thương Hiệu Mới'}</h2>
            <button onClick={resetForm} className="btn-close">
              ✕
            </button>
          </div>

          <form onSubmit={handleSubmit} className="admin-form">
            <div className="form-group">
              <label htmlFor="name">
                Tên thương hiệu <span className="required">*</span>
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
              <label htmlFor="description">Mô tả</label>
              <textarea
                id="description"
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows="3"
                className="form-control"
              />
            </div>

            <div className="form-group">
              <label htmlFor="image">Logo thương hiệu</label>
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

            <div className="form-actions">
              <button type="button" onClick={resetForm} className="btn-secondary">
                Hủy
              </button>
              <button type="submit" className="btn-primary">
                {editingId ? 'Cập nhật' : 'Tạo mới'}
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="admin-grid">
        {brands.length === 0 ? (
          <p className="no-data">Chưa có thương hiệu nào</p>
        ) : (
          brands.map((brand) => (
            <div key={brand.id} className="admin-card">
              {brand.image_url && (
                <div className="card-image">
                  <img src={brand.image_url} alt={brand.name} />
                </div>
              )}
              <div className="card-content">
                <h3>{brand.name}</h3>
                {brand.description && <p>{brand.description}</p>}
                <div className="card-actions">
                  <button
                    onClick={() => handleEdit(brand)}
                    className="btn-icon btn-edit"
                  >
                    Sửa
                  </button>
                  <button
                    onClick={() => handleDelete(brand.id)}
                    className="btn-icon btn-delete"
                  >
                    Xóa
                  </button>
                </div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

export default Brands;
