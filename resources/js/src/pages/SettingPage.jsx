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

const discountFilter = [
    {
        value: '0',
        name: 'All',
    },
    {
        value: '1',
        name: '>= 20%',
    },
];
export default function SettingPage() {
    const [googleDay1, setGoogleDay1] = useState({});
    const [googleDay2, setGoogleDay2] = useState({});
    const [amazonDay1, setAmazonDay1] = useState({});
    const [amazonDay2, setAmazonDay2] = useState({});

    const [amazonMainMail, setAmazonMainMail] = useState({});
    const [amazonAgentMail, setAmazonAgentMail] = useState({});
    const [amazonFilter, setAmazonFilter] = useState({});

    const [googleMainMail, setGoogleMainMail] = useState({});
    const [googleAgentMail, setGoogleAgentMail] = useState({});
    const [googleFilter, setGoogleFilter] = useState({});

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

            const aMainMail = setting.find(item => {
                return item.category == 'mail' && item.name == 'amazon_main';
            });

            const aAgentMail = setting.find(item => {
                return item.category == 'mail' && item.name == 'amazon_agent';
            });            
            
            const aFilter = setting.find(item => {
                return item.category == 'discount' && item.name == 'amazon';
            });

            const gMainMail = setting.find(item => {
                return item.category == 'mail' && item.name == 'google_main';
            });

            const gAgentMail = setting.find(item => {
                return item.category == 'mail' && item.name == 'google_agent';
            });            
            
            const gFilter = setting.find(item => {
                return item.category == 'discount' && item.name == 'google';
            });

            setGoogleDay1(gd1);
            setGoogleDay2(gd2);
            setAmazonDay1(ad1);
            setAmazonDay2(ad2);

            setAmazonMainMail(aMainMail);
            setAmazonAgentMail(aAgentMail);
            setAmazonFilter(aFilter);

            setGoogleMainMail(gMainMail);
            setGoogleAgentMail(gAgentMail);
            setGoogleFilter(gFilter);
        }
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
            case 'amazon_main_mail':
                let checked = e.target.checked ? '1' : '0';
                const aMMail = {
                    'id': amazonMainMail.id,
                    'category': amazonMainMail.category,
                    'name': amazonMainMail.name,
                    value: checked
                };
                updateSetting(aMMail);
                setAmazonMainMail(aMMail);
                break;                
            case 'amazon_agent_mail':
                let checked_aa = e.target.checked ? '1' : '0';
                const aAMail = {
                    'id': amazonAgentMail.id,
                    'category': amazonAgentMail.category,
                    'name': amazonAgentMail.name,
                    value: checked_aa
                };
                updateSetting(aAMail);
                setAmazonAgentMail(aAMail);
                break;
            case 'amazon_filter':
                const af = {
                    'id': amazonFilter.id,
                    'category': amazonFilter.category,
                    'name': amazonFilter.name,
                    value: value
                };
                updateSetting(af);
                setAmazonFilter(af);
                break;                             
            case 'google_main_mail':
                let checked_g = e.target.checked ? '1' : '0';
                const gMMail = {
                    'id': googleMainMail.id,
                    'category': googleMainMail.category,
                    'name': googleMainMail.name,
                    value: checked_g
                };
                updateSetting(gMMail);
                setGoogleMainMail(gMMail);
                break;                
            case 'google_agent_mail':
                let checked_ga = e.target.checked ? '1' : '0';
                const gAMail = {
                    'id': googleAgentMail.id,
                    'category': googleAgentMail.category,
                    'name': googleAgentMail.name,
                    value: checked_ga
                };
                updateSetting(gAMail);
                setGoogleAgentMail(gAMail);
                break;
            case 'google_filter':
                const gf = {
                    'id': googleFilter.id,
                    'category': googleFilter.category,
                    'name': googleFilter.name,
                    value: value
                };
                updateSetting(gf);
                setGoogleFilter(gf);
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

            <div className="mt-8">
                <label>Auto Mail Setting ( Amazon ) </label>

                <div className="p-5 grid grid-cols-12 gap-6 border border-spacing-4 rounded-lg">
                    {/* <div className="col-span-4">
                        <label htmlFor="amazon_main_mail">Main Mail</label>
                        <div>
                            <input
                                type="checkbox"
                                id="amazon_main_mail"
                                name="amazon_main_mail"
                                className="bg-transparent border rounded-sm p-1.5"
                                onChange={onChangeSelect}
                                value="amazon_main_mail"
                                checked={amazonMainMail.value == '1' ? true : false}
                            />
                        </div>
                    </div>
                    <div className="col-span-4">
                        <label htmlFor="amazon_agent_mail">Sales Agent Mail</label>
                        <div>
                            <input
                                type="checkbox"
                                id="amazon_agent_mail"
                                name="amazon_agent_mail"
                                className="bg-transparent border rounded-sm p-1.5"
                                onChange={onChangeSelect}
                                value="amazon_agent_mail"
                                checked={amazonAgentMail.value == '1' ? true : false}
                            />
                        </div>
                    </div> */}
                    <div className="col-span-4">
                        <label>Discount Filter</label>
                        <div>
                            <select
                                className="bg-transparent border rounded-sm p-1.5"
                                name='amazon_filter'
                                onChange={onChangeSelect}
                                value={amazonFilter.value}
                            >
                                {
                                    discountFilter.map((item) => (
                                        <option key={item.value} value={item.value}>{item.name}</option>
                                    ))
                                }
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div className="mt-8">
                <label>Auto Mail Setting ( Google ) </label>

                <div className="p-5 grid grid-cols-12 gap-6 border border-spacing-4 rounded-lg">
                    {/* <div className="col-span-4">
                        <label htmlFor="google_main_mail">Main Mail</label>
                        <div>
                            <input
                                type="checkbox"
                                id="google_main_mail"
                                name="google_main_mail"
                                className="bg-transparent border rounded-sm p-1.5"
                                onChange={onChangeSelect}
                                value="google_main_mail"
                                checked={googleMainMail.value == '1' ? true : false}
                            />
                        </div>
                    </div>
                    <div className="col-span-4">
                        <label htmlFor="google_agent_mail">Sales Agent Mail</label>
                        <div>
                            <input
                                type="checkbox"
                                id="google_agent_mail"
                                name="google_agent_mail"
                                className="bg-transparent border rounded-sm p-1.5"
                                onChange={onChangeSelect}
                                value="google_agent_mail"
                                checked={googleAgentMail.value == '1' ? true : false}
                            />
                        </div>
                    </div> */}
                    <div className="col-span-4">
                        <label>Discount Filter</label>
                        <div>
                            <select
                                className="bg-transparent border rounded-sm p-1.5"
                                name='google_filter'
                                onChange={onChangeSelect}
                                value={googleFilter.value}
                            >
                                {
                                    discountFilter.map((item) => (
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
