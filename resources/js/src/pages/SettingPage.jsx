import React, { useState, useReducer, useEffect, useRef } from "react";
import axios from "../services/axios";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const weekDays = [
    {
        value: -1,
        name: 'Diable',
    },
    {
        value: 0,
        name: 'Sunday',
    },
    {
        value: 1,
        name: 'Monday',
    },
    {
        value: 2,
        name: 'Tuesday',
    },
    {
        value: 3,
        name: 'Wednsday',
    },
    {
        value: 4,
        name: 'Thursday',
    },
    {
        value: 5,
        name: 'Friday',
    },
    {
        value: 6,
        name: 'Saturday',
    },
];
export default function SettingPage() {
    const [googleDay1, setGoogleDay1] = useState({});
    const [googleDay2, setGoogleDay2] = useState({});
    const [amazonDay1, setAmazonDay1] = useState({});
    const [amazonDay2, setAmazonDay2] = useState({});

    async function getData() {
        const res = await axios.post('/setting/getSetting', {});
        if (res.status == 200) {
            const setting = res.data.data;
            const gd1 = setting.find(item => {
                return item.category == 'cron' && item.name == 'google_week1';
            });
            const gd2 = setting.find(item => {
                return item.category == 'cron' && item.name == 'google_week2';
            });
            const ad1 = setting.find(item => {
                return item.category == 'cron' && item.name == 'amazon_week1';
            });
            const ad2 = setting.find(item => {
                return item.category == 'cron' && item.name == 'amazon_week2';
            });
            setGoogleDay1(gd1);
            setGoogleDay2(gd2);
            setAmazonDay1(ad1);
            setAmazonDay2(ad2);
        }
        console.log(res);
    }

    useEffect(() => {
        getData();
    }, []);

    const onChangeSelect = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        console.log(name, value);
        switch (name) {
            case 'google_week1':
                const tmp = {
                    'id': googleDay1.id,
                    'category': googleDay1.category,
                    'name': googleDay1.name,
                    value: value
                };
                updateSetting(tmp);
                setGoogleDay1(tmp);
                break;
            case 'google_week2':
                const tmp2 = {
                    'id': googleDay2.id,
                    'category': googleDay2.category,
                    'name': googleDay2.name,
                    value: value
                };
                updateSetting(tmp2);
                setGoogleDay2(tmp2);
                break;
            case 'amazon_week1':
                const a1 = {
                    'id': amazonDay1.id,
                    'category': amazonDay1.category,
                    'name': amazonDay1.name,
                    value: value
                };
                updateSetting(a1);
                setAmazonDay1(a1);
                break;
            case 'amazon_week2':
                const a2 = {
                    'id': amazonDay2.id,
                    'category': amazonDay2.category,
                    'name': amazonDay2.name,
                    value: value
                };
                updateSetting(a2);
                setAmazonDay2(a2);
                break;

            default:
                break;
        }
    }

    const updateSetting = async (data) => {
        const ret = await axios.post('/setting/updateSetting', data);
        if (ret.status == 200) {
            toast.success('Updated Successfully!', {
                position: "bottom-right",
                autoClose: 5000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
                progress: undefined,
                theme: "colored",
                });
            getData();
        } else {

        }
    }
    return (
        <div className="p-2">
            <div className="h-2" />
            <div className="mb-3 text-lg font-bold"><h1>Settings</h1></div>
            <div>
                <label>Google Scraping Day of Week</label>

                <div className="p-5 grid grid-cols-12 gap-6 border border-spacing-4 rounded-lg">
                    <div className="col-span-6">
                        <label>Day 1</label>
                        <div>
                            <select
                                className="bg-transparent border rounded-sm p-1.5"
                                value={googleDay1.value}
                                name={googleDay1.name}
                                onChange={onChangeSelect}
                            >
                                {
                                    weekDays.map((item) => (
                                        <option key={item.value} value={item.value}>{item.name}</option>
                                    ))
                                }
                            </select>
                        </div>
                    </div>
                    <div className="col-span-6">
                        <label>Day 2</label>
                        <div>
                            <select
                                className="bg-transparent border rounded-sm p-1.5"
                                name={googleDay2.name}
                                onChange={onChangeSelect}
                                value={googleDay2.value}
                            >
                                {
                                    weekDays.map((item) => (
                                        <option key={item.value} value={item.value}>{item.name}</option>
                                    ))
                                }
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div className="mt-8">
                <label>Amazon Scraping Day of Week</label>

                <div className="p-5 grid grid-cols-12 gap-6 border border-spacing-4 rounded-lg">
                    <div className="col-span-6">
                        <label>Day 1</label>
                        <div>
                            <select
                                className="bg-transparent border rounded-sm p-1.5"
                                name={amazonDay1.name}
                                onChange={onChangeSelect}
                                value={amazonDay1.value}
                            >
                                {
                                    weekDays.map((item) => (
                                        <option key={item.value} value={item.value}>{item.name}</option>
                                    ))
                                }
                            </select>
                        </div>
                    </div>
                    <div className="col-span-6">
                        <label>Day 2</label>
                        <div>
                            <select
                                className="bg-transparent border rounded-sm p-1.5"
                                name={amazonDay2.name}
                                onChange={onChangeSelect}
                                value={amazonDay2.value}
                            >
                                {
                                    weekDays.map((item) => (
                                        <option key={item.value} value={item.value}>{item.name}</option>
                                    ))
                                }
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <ToastContainer />
        </div>
    );
}
