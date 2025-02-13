import {
    Admin,
    Resource,
    ListGuesser,
    ShowGuesser,
} from "react-admin";
import { Layout } from "./Layout";
import { dataProvider } from "./dataProvider";
import { authProvider } from "./authProvider";

import OrderList from "./pages/orders/list";
import OrderShow from "./pages/orders/show";
import OrderEdit from "./pages/orders/edit";
import OrderCreate from "./pages/orders/create";

export const App = () => (
    <Admin layout={Layout} dataProvider={dataProvider} authProvider={authProvider}>
        <Resource name="orders" list={OrderList} show={OrderShow} edit={OrderEdit} create={OrderCreate} />
        <Resource name="products" list={ListGuesser} show={ShowGuesser} />
    </Admin>
);
