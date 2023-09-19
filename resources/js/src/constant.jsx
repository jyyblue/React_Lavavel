const { VITE_APP_ENV, VITE_APP_URL_LOCAL, VITE_APP_URL_PRODUCT } = import.meta.env;

export const server_url = VITE_APP_ENV === 'local'? VITE_APP_URL_LOCAL: VITE_APP_URL_PRODUCT;
export const api_url = server_url + 'api';