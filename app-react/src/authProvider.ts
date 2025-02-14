import { AuthProvider } from "react-admin";

export const authProvider: AuthProvider = {
    login: async ({ username, password }) => {
        const request = new Request('http://localhost/api/login', {
            method: 'POST',
            body: JSON.stringify({ email: username, password: password }),
            headers: new Headers({ 'Accept': 'application/json', 'Accept-Encoding': 'gzip, deflate, br', 'Connection': 'keep-alive', 'Content-Type': 'application/json' }),
        });

        const response = await fetch(request);
        if (response.status < 200 || response.status >= 300) {
            throw new Error(response.statusText);
        }

        const { token } = await response.json();
        localStorage.setItem('username', username);
        localStorage.setItem('token', token);
        return Promise.resolve();
    },
    logout: () => {
        localStorage.removeItem("username");
        localStorage.removeItem("token");
        return Promise.resolve();
    },
    checkError: ({ status }: {status: number}) => {
        if (status === 401 || status === 403) {
            localStorage.removeItem("username");
            localStorage.removeItem("token");
            return Promise.reject();
        }
        return Promise.resolve();
    },
    checkAuth: () => {
        return localStorage.getItem("username") ? Promise.resolve() : Promise.reject();
    },
    getPermissions: () => {
        return Promise.resolve();
    },
};