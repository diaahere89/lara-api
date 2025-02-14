import { ArrayField, Datagrid, DateField, NumberField, ReferenceField, Show, SimpleShowLayout, TextField } from 'react-admin';

const OrderShow = () => (
    <Show>
        <SimpleShowLayout>
            <TextField source="name" />
            <TextField source="description" />
            <DateField source="date" />
            <DateField source="created_at" />
            <DateField source="updated_at" />
            <ArrayField source="products">
                <Datagrid>
                    <ReferenceField source="product_id" reference="products" />
                    <TextField source="product_name" />
                    <NumberField source="quantity" />
                </Datagrid>
            </ArrayField>
        </SimpleShowLayout>
    </Show>
);

export default OrderShow;