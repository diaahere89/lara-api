import { ArrayInput, DateInput, Edit, NumberInput, ReferenceInput, SimpleForm, SimpleFormIterator, TextInput } from 'react-admin';

const OrderEdit = () => (
    <Edit>
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
    </Edit>
);

export default OrderEdit;