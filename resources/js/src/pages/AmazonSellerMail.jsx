import React, { useState, useReducer, useEffect, useRef } from "react";
import axios from "../services/axios";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import Select from 'react-select';
import AmazonDiscount from "../component/AmazonDiscount";
import AmazonMailTable from "../component/AmazonMailTable";

const options = [
    {
        'label': 'All',
        'value': 0,
    },
    {
        'label': '>= 20%',
        'value': 1,
    },
];
export default function AmazonSellerMail() {
    const [sellerId, setSellerId] = useState(null);
    const [selectSeller, setSelectSeller] = useState(null);
    const [sellerData, setSellerData] = useState([]);
    const [reloadMailTable, setReloadMailTable] = useState(1);

    const all = options.find(item => item.value == 0);
    const [discount, setDiscount] = useState(all);

    useEffect(() => {
        if(selectSeller != null) {
            setSellerId(selectSeller.value);
        }
    }, [selectSeller]);

    async function getData() {
        const resp = await axios.post('/getAmazonSeller', {});
        if (resp.status == 200) {
            let temp = [];
            console.log(resp.data.data)
            resp.data.data.forEach(element => {
                let name = element.name;
                console.log(name);
                if(name == null) {
                    name = element.amazon_id
                }
                if (element.email) {
                    const item = {
                        'value': element.amazon_id,
                        'label': name,
                    };
                    temp.push(item);
                }
            });
            setSellerData(temp);
        }
    }

    const onClickSend = async () => {
        if (selectSeller == null) {
            return;
        }
        const data = {
            'id': selectSeller.value,
            'discount': discount.value,
        };
        const ret = await axios.post('/sendAmazonMail', data);
        if (ret.status == 200) {
            setReloadMailTable(Math.random());
        }
    }

    useEffect(() => {
        getData();
    }, []);

    return (
        <div className="p-2">
            <div className="h-2" />
            <div className="mb-3 text-lg font-bold"><h1>Mail / Amazon</h1></div>
            <label>Sellers </label>
            <div>
                <div className="grid grid-cols-12 gap-4">
                    <Select
                        defaultValue={selectSeller}
                        onChange={setSelectSeller}
                        options={sellerData}
                        className="w-full col-span-7"
                    />
                    <Select
                        defaultValue={discount}
                        onChange={setDiscount}
                        options={options}
                        className="w-full col-span-3"
                    />
                    <button className="col-span-2
                dark:bg-primary border border-primary cursor-pointer
                dark:hover:bg-opacity-80 p-1 rounded-lg text-white transition"
                        onClick={onClickSend}
                    >Send</button>
                </div>
            </div>
            <div className="grid grid-cols-12">
                <div className="col-span-12">
                    <AmazonDiscount sellerId = {sellerId} discount = {discount.value} />
                </div>
                <div className="col-span-12 mt-8">
                    <AmazonMailTable reload={reloadMailTable} />
                </div>
            </div>
            <ToastContainer />
        </div>
    );
}
