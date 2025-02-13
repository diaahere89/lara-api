import { fetchUtils } from 'react-admin';
import { stringify } from 'query-string';

const apiUrl = import.meta.env.VITE_JSON_SERVER_URL; // Change this based on your backend URL
const httpClient = (url: string, options: any = {}) => {
    if (!options.headers) {
        options.headers = new Headers({ 'Accept': "application/json", 'Accept-Encoding': "gzip, deflate, br", 'Connection': "keep-alive", "Content-Type": "application/json" });
    }
    const token = localStorage.getItem("token");
    //console.log("dataOrivuder storage token", token);
    if (token) {
        options.headers.set("Authorization", `Bearer ${token}`);
    }
    return fetchUtils.fetchJson(url, options);
};


export const dataProvider = {
    getList: async (resource, params) => {
        const { page, perPage } = params.pagination;
        const { field, order } = params.sort;
        const query = {
            ...fetchUtils.flattenObject(params.filter),
            _sort: field,
            _order: order,
            _start: (page - 1) * perPage,
            _end: page * perPage,
        };
        const url = `${apiUrl}/${resource}?${stringify(query)}`;

        const { json, headers } = await httpClient(url);
        return {
            data: json,
            total: parseInt(headers.get('X-Total-Count'), 10),
        };
    },
    // Other methods (getOne, getMany, etc.)
};
