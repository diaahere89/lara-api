import { ArrayField, Datagrid, DateField, List, TextField, EditButton, TextInput, ReferenceInput } from 'react-admin';

const OrderList = () => {
    const orderFilters = [
        <TextInput source="name" label="Order title" alwaysOn />,
        <TextInput source="description" label="Order description" alwaysOn />,
        <ReferenceInput source="product_id" reference="products" label="Product ID" />,
    ];

    return (
        <List filters={orderFilters}>
            <Datagrid>
                <TextField source="name" label="Order title" />
                <TextField source="description" />
                <DateField source="date" showTime={false} locales="en-GB" label="Submitted on" />
                <ArrayField source="products" label="Order Details">
                    <Datagrid bulkActionButtons={false}>
                        {/* <TextField source="product_id" label="Product ID" /> */}
                        <TextField source="product_name" label="Product Name" />
                        <TextField source="quantity" label="Quantity" />
                    </Datagrid>
                </ArrayField>
                <EditButton />
            </Datagrid>
        </List>
    );
};

export default OrderList;