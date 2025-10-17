import axios from 'axios';

// Get the FreshService API base URL from a global variable or config
const getFreshServiceApiUrl = window.getFreshServiceApiUrl || '';

/**
 * Generic API request using axios
 * @param {string} url - The endpoint URL
 * @param {object} options - Axios request config (method, headers, data, etc.)
 * @returns {Promise<any>} - The response data
 */
export async function apiFetch(url) {
  try {
    const response = await axios.get(url, {
      headers: {
        'Content-Type': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : error;
  }
}
