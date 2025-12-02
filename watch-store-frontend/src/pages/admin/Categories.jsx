import { useState, useEffect } from 'react';
import categoryService from '../../services/categoryService';

const Categories = () => {
  const [categories, setCategories] = useState([]);
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
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      setLoading(true);
      const response = await categoryService.getCategories();
      setCategories(response.data || []);
    } catch (error) {
      console.error('Error fetching categories:', error);
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
        await categoryService.updateCategory(editingId, submitData);
        alert('Cập nhật danh mục thành công!');
      } else {
        await categoryService.createCategory(submitData);
        alert('Tạo danh mục thành công!');
      }

      resetForm();
      fetchCategories();
    } catch (error) {
      console.error('Error saving category:', error);
      alert(
        `Không thể ${editingId ? 'cập nhật' : 'tạo'} danh mục: ${
          error.response?.data?.message || error.message
        }`
      );
    }
  };

  const handleEdit = (category) => {
    setEditingId(category.id);
    setFormData({
      name: category.name,
      description: category.description || '',
      image: null,
    });
    setImagePreview(category.image_url);
    setShowForm(true);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Bạn có chắc muốn xóa danh mục này?')) {
      return;
    }

    try {
      await categoryService.deleteCategory(id);
      fetchCategories();
      alert('Xóa danh mục thành công!');
    } catch (error) {
      console.error('Error deleting category:', error);
      alert('Không thể xóa danh mục');
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
    <div className="admin-categories">
      <div className="admin-header">
        <h1>Quản Lý Danh Mục</h1>
        <button
          onClick={() => {
            resetForm();
            setShowForm(true);
          }}
          className="btn-primary"
        >
          Thêm Danh Mục Mới
        </button>
      </div>

      {showForm && (
        <div className="admin-form-card">
          <div className="form-header">
            <h2>{editingId ? 'Sửa Danh Mục' : 'Thêm Danh Mục Mới'}</h2>
            <button onClick={resetForm} className="btn-close">
              ✕
            </button>
          </div>

          <form onSubmit={handleSubmit} className="admin-form">
            <div className="form-group">
              <label htmlFor="name">
                Tên danh mục <span className="required">*</span>
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
        {categories.length === 0 ? (
          <p className="no-data">Chưa có danh mục nào</p>
        ) : (
          categories.map((category) => (
            <div key={category.id} className="admin-card">
              {category.image_url && (
                <div className="card-image">
                  <img src={category.image_url} alt={category.name} />
                </div>
              )}
              <div className="card-content">
                <h3>{category.name}</h3>
                {category.description && <p>{category.description}</p>}
                <div className="card-actions">
                  <button
                    onClick={() => handleEdit(category)}
                    className="btn-icon btn-edit"
                  >
                    Sửa
                  </button>
                  <button
                    onClick={() => handleDelete(category.id)}
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

export default Categories;
