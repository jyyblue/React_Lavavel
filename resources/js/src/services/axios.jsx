import Axios from 'axios';
import { api_url } from '../constant';
const axios = Axios.create({
	baseURL: api_url,
	withCredentials: true,
	headers: {
		"Content-Type": "application/json",
		"Accept": "application/json",
	},
});

export default axios;