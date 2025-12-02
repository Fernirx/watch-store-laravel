import axios from '../api/axiosConfig';

const productService = {
  // Lấy danh sách sản phẩm
  getProducts: async (params = {}) => {
    const response = await axios.get('/products', { params });
    return response.data;
  },

  // Lấy chi tiết sản phẩm
  getProduct: async (id) => {
    const response = await axios.get(`/products/${id}`);
    return response.data;
  },

  // Lấy danh mục
  getCategories: async () => {
    const response = await axios.get('/categories');
    return response.data;
  },

  // Lấy thương hiệu
  getBrands: async () => {
    const response = await axios.get('/brands');
    return response.data;
  },
};

export default productService;
