import React, { useState, useReducer, useEffect, useRef } from "react";
import axios from "../services/axios";
import {
    useReactTable,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    flexRender,
} from "@tanstack/react-table";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { api_url } from "../constant";
// Give our default column cell renderer editing superpowers!
const defaultColumn = {
    cell: ({ getValue, row: { index }, column: { id }, table }) => {
        const initialValue = getValue();
        // We need to keep and update the state of the cell normally
        const [value, setValue] = useState(initialValue);

        // When the input is blurred, we'll call our table meta's updateData function
        const onBlur = () => {
            if(initialValue !== value) {
                table.options.meta?.updateData(index, id, value);
            }
        };
        
        const onChangeSelect = (e) => {
            let checked = e.target.checked;
            let _value = checked ? '1': '0';
            setValue(_value);
            if (initialValue !== _value) {
                table.options.meta?.updateData(index, id, _value);
            }
        };

        // If the initialValue is changed external, sync it up with our state
        useEffect(() => {
            setValue(initialValue);
        }, [initialValue]);

        if(id == 'email' || id == 'sales_agent_name' || id == 'sales_agent_email') {
            return (
                <input
                    className="w-full border-b border-stroke bg-transparent py-3 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-gray-100 dark:focus:border-primary"
                    value={value}
                    onChange={(e) => setValue(e.target.value)}
                    onBlur={onBlur}
                />
            );
        }else if (id == "email_flg" || id == "agent_flg") {

            return (
                <div
                className="w-full border-b border-stroke bg-transparent py-3 pl-1 pr-1 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-gray-100 dark:focus:border-primary"
                >
                    <input
                        type="checkbox"
                        className="w-full"
                        onChange={(e) => onChangeSelect(e)}
                        value={value}
                        checked={value == "1" ? true : false}
                    />
                </div>
            );
        }else {
            return (
                <input
                    className="w-full border-b border-stroke bg-transparent py-3 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                    value={value}
                    disabled
                    onChange={(e) => setValue(e.target.value)}
                    onBlur={onBlur}
                />
            );
        }
    },
};

function useSkipper() {
    const shouldSkipRef = React.useRef(true);
    const shouldSkip = shouldSkipRef.current;
    // Wrap a function with this to skip a pagination reset temporarily
    const skip = React.useCallback(() => {
        shouldSkipRef.current = false;
    }, []);

    useEffect(() => {
        shouldSkipRef.current = true;
    });

    return [shouldSkip, skip];
}

export default function GoogleSeller() {
    const [importFile, setImportFile] = useState('');
    const openExport = () => {
        window.open(api_url +'/googleSeller/export', '_blank');
    }
    const inputFile = useRef(null) 
    const onImportClick = () => {
        // `current` points to the mounted file input element
        inputFile.current.click();
    };
    const handleFileChange = (e) => {
        if (e.target.files) {
          handleUploadClick(e.target.files[0]);
        }
    };
    const handleUploadClick = async (file) => {
        if (!file) {
            toast('File is empty');
          return;
        }
        let config = {
            headers: {
                'content-type': 'multipart/form-data'
            }
          }
        let importData = new FormData();
        importData.append("file", file);
        // 👇 Uploading the file using the fetch API to the server
        axios.post(api_url + '/googleSeller/import', importData, config).then((res) => {
            console.log(res);
            toast('Imported Successfully!');
            setImportFile('');
            getData();
        });
    };

    const columns = React.useMemo(
        () => [
            {
                header: "Name",
                // accessorKey: "name",
                accessorFn: row => row.name,
                id: 'name',
                footer: (props) => props.column.id,
            },
            {
                header: "Email",
                accessorFn: row => row.email,
                id: 'email',
                footer: (props) => props.column.id,
            },
            {
                header: "Send Auto Email",
                accessorFn: (row) => row.email_flg,
                id: "email_flg",
                footer: (props) => props.column.id,
            },
            {
                header: "SALES AGENT NAME",
                accessorFn: row => row.sales_agent_name,
                id: 'sales_agent_name',
                footer: (props) => props.column.id,
            },
            {
                header: "SALES AGENT EMAIL",
                accessorFn: row => row.sales_agent_email,
                id: 'sales_agent_email',
                footer: (props) => props.column.id,
            },
            {
                header: "Send AGENT EMAIL",
                accessorFn: (row) => row.agent_flg,
                id: "agent_flg",
                footer: (props) => props.column.id,
            },
        ],
        []
    );
    const [data, setData] = useState([]);
    async function getData () {
        const resp = await axios.post('/getGoogleSeller', {});
        if(resp.status == 200) {
            setData(resp.data.data);
        }
        console.log(resp);
    }
    useEffect(()=> {
        getData();
    }, []);

    const [autoResetPageIndex, skipAutoResetPageIndex] = useSkipper();

    const table = useReactTable({
        data,
        columns,
        defaultColumn,
        getCoreRowModel: getCoreRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        autoResetPageIndex,
        // Provide our updateData function to our table meta
        meta: {
            updateData: async (rowIndex, columnId, value) => {
                // Skip page index reset until after next rerender
                skipAutoResetPageIndex();
                console.log(rowIndex, columnId, value);
                
                setData((old) =>
                    old.map((row, index) => {
                        if (index === rowIndex) {
                            let data = old[rowIndex];
                            data[columnId] = value;
                            const f = async (data)=> {
                                const resp = await axios.post('/updateGoogleSeller', data);
                                return resp;
                            }
                            f(data).then((ret) => {
                                toast('Updated Successfully!', {
                                    position: "bottom-right",
                                    autoClose: 1000,
                                    hideProgressBar: false,
                                    closeOnClick: true,
                                    pauseOnHover: true,
                                    draggable: true,
                                    progress: undefined,
                                    theme: "colored",
                                    });
                                return {
                                    ...old[rowIndex],
                                    [columnId]: value,
                                };
                            });
                                
                        }
                        return row;
                    })
                );
            },
        },
        debugTable: true,
    });
    return (
        <div className="p-2">
            <div className="h-2" />
            <div className="mb-3 text-lg font-bold"><h1>Sellers / Google</h1></div>
            <div>
                <button className="
                dark:bg-primary border border-primary cursor-pointer
                dark:hover:bg-opacity-80 p-3 rounded-lg text-white transition mr-3"
                onClick={openExport}
                >Export</button>
                <button className="
                dark:bg-primary border border-primary cursor-pointer
                dark:hover:bg-opacity-80 p-3 rounded-lg text-white transition"
                onClick={onImportClick}
                >Import</button>
                <input
                onChange={handleFileChange} 
                type='file' 
                id='file' 
                accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                ref={inputFile} 
                style={{display: 'none'}}
                value={importFile}
                />
            </div>
            <table className="w-full">
                <thead>
                    {table.getHeaderGroups().map((headerGroup) => (
                        <tr key={headerGroup.id}>
                            {headerGroup.headers.map((header) => {
                                return (
                                    <th
                                        key={header.id}
                                        colSpan={header.colSpan}
                                    >
                                        {header.isPlaceholder ? null : (
                                            <div>
                                                {flexRender(
                                                    header.column.columnDef
                                                        .header,
                                                    header.getContext()
                                                )}
                                                {header.column.getCanFilter() ? (
                                                    <div>
                                                        <Filter
                                                            column={
                                                                header.column
                                                            }
                                                            table={table}
                                                        />
                                                    </div>
                                                ) : null}
                                            </div>
                                        )}
                                    </th>
                                );
                            })}
                        </tr>
                    ))}
                </thead>
                <tbody>
                    {table.getRowModel().rows.map((row) => {
                        return (
                            <tr key={row.id}>
                                {row.getVisibleCells().map((cell) => {
                                    return (
                                        <td key={cell.id}>
                                            {flexRender(
                                                cell.column.columnDef.cell,
                                                cell.getContext()
                                            )}
                                        </td>
                                    );
                                })}
                            </tr>
                        );
                    })}
                </tbody>
            </table>
            <div className="h-2" />
            <div className="flex items-center gap-2">
                <button
                    className="border rounded p-1"
                    onClick={() => table.setPageIndex(0)}
                    disabled={!table.getCanPreviousPage()}
                >
                    {"<<"}
                </button>
                <button
                    className="border rounded p-1"
                    onClick={() => table.previousPage()}
                    disabled={!table.getCanPreviousPage()}
                >
                    {"<"}
                </button>
                <button
                    className="border rounded p-1"
                    onClick={() => table.nextPage()}
                    disabled={!table.getCanNextPage()}
                >
                    {">"}
                </button>
                <button
                    className="border rounded p-1"
                    onClick={() => table.setPageIndex(table.getPageCount() - 1)}
                    disabled={!table.getCanNextPage()}
                >
                    {">>"}
                </button>
                <span className="flex items-center gap-1">
                    <div>Page</div>
                    <strong>
                        {table.getState().pagination.pageIndex + 1} of{" "}
                        {table.getPageCount()}
                    </strong>
                </span>
                <span className="flex items-center gap-1">
                    | Go to page:
                    <input
                        type="number"
                        defaultValue={table.getState().pagination.pageIndex + 1}
                        onChange={(e) => {
                            const page = e.target.value
                                ? Number(e.target.value) - 1
                                : 0;
                            table.setPageIndex(page);
                        }}
                        style={{
                            paddingTop: "0.33rem",
                            paddingBottom: "0.33rem",
                        }}
                        className="w-16 rounded-lg border border-stroke bg-transparent outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                    />
                </span>
                <select
                    value={table.getState().pagination.pageSize}
                    onChange={(e) => {
                        table.setPageSize(Number(e.target.value));
                    }}
                    className="rounded-lg border border-stroke bg-transparent py-2 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                >
                    {[10, 20, 30, 40, 50].map((pageSize) => (
                        <option key={pageSize} value={pageSize}>
                            Show {pageSize}
                        </option>
                    ))}
                </select>
            </div>
            <ToastContainer />
        </div>
    );
}

function Filter({ column, table }) {
    const firstValue = table
        .getPreFilteredRowModel()
        .flatRows[0]?.getValue(column.id);

    const columnFilterValue = column.getFilterValue();

    return (
        <input
            className="w-full rounded-lg border border-stroke bg-transparent py-3 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            type="text"
            value={columnFilterValue ?? ""}
            onChange={(e) => column.setFilterValue(e.target.value)}
            placeholder={`Search...`}
        />
    );
    return typeof firstValue === "number" ? (
        <div className="flex space-x-2">
            <input
                type="number"
                value={columnFilterValue?.[0] ?? ""}
                onChange={(e) =>
                    column.setFilterValue((old) => [e.target.value, old?.[1]])
                }
                placeholder={`Min`}
                className="w-full rounded-lg border border-stroke bg-transparent py-3 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            />
            <input
                type="number"
                value={columnFilterValue?.[1] ?? ""}
                onChange={(e) =>
                    column.setFilterValue((old) => [old?.[0], e.target.value])
                }
                placeholder={`Max`}
                className="w-full rounded-lg border border-stroke bg-transparent py-3 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            />
        </div>
    ) : (
        <input
            className="w-full rounded-lg border border-stroke bg-transparent py-3 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
            type="text"
            value={columnFilterValue ?? ""}
            onChange={(e) => column.setFilterValue(e.target.value)}
            placeholder={`Search...`}
        />
    );
}
