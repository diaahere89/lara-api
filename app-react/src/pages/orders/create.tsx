import { ArrayInput, DateInput, Create, NumberInput, ReferenceInput, SimpleForm, SimpleFormIterator, TextInput } from 'react-admin';

const OrderCreate = () => (
    <Create>
        <SimpleForm>
            <TextInput source="name" />
            <TextInput source="description" />
            <DateInput source="date" />
            <ArrayInput source="products">
                <SimpleFormIterator>
                    <ReferenceInput source="product_id" reference="products" />
                    <TextInput source="product_name" disabled />
                    <NumberInput source="quantity" />
                </SimpleFormIterator>
            </ArrayInput>
        </SimpleForm>
    </Create>
);

export default OrderCreate;