import React, { useState } from "react";
import 'react-toastify/dist/ReactToastify.css';
import GoogleHistoryTable from "../component/GoogleHistoryTable";

export default function GoogleScrapeHistory() {
    const [reloadMailTable, setReloadMailTable] = useState(1);

    const onClickRefresh = async () => {
        setReloadMailTable(Math.random());
    }

    return (
        <div className="p-2">
            <div className="h-2" />
            <div className="mb-3 text-lg font-bold"><h1>History / Google</h1></div>
            <div>
                <div className="grid grid-cols-12 gap-4">
                    <button className="col-span-2
                dark:bg-primary border border-primary cursor-pointer
                dark:hover:bg-opacity-80 p-1 rounded-lg text-white transition"
                        onClick={onClickRefresh}
                    >Refresh</button>
                </div>
            </div>
            <div className="grid grid-cols-12">
                <div className="col-span-12 mt-8">
                    <GoogleHistoryTable reload={reloadMailTable} />
                </div>
            </div>
        </div>
    );
}
