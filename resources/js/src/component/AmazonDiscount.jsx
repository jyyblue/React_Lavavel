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
// Give our default column cell renderer editing superpowers!
const defaultColumn = {
    cell: ({ getValue, row: { index }, column: { id }, table }) => {
        const initialValue = getValue();

        if(id == 'offer_link') {
            return (
                <a 
                className="w-full border-b border-stroke bg-transparent py-4 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                href={initialValue} target="_blank">Link</a>
            )
        }
        return (
            <input
                className="w-full border-b border-stroke bg-transparent py-3 pl-6 pr-10 outline-none focus:border-primary focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                value={initialValue}
                disabled
            />
        );
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

export default function AmazonDiscount() {
    const columns = React.useMemo(
        () => [
            {
                header: "SKU",
                accessorFn: row => row.sku,
                id: 'sku',
                footer: (props) => props.column.id,
            },
            {
                header: "SELLER NAME",
                accessorFn: row => row.seller,
                id: 'seller',
                footer: (props) => props.column.id,
            },
            {
                header: "PRODUCT NAME",
                accessorFn: row => row.title,
                id: 'title',
                footer: (props) => props.column.id,
            },
            {
                header: "MSRP Price ",
                accessorFn: row => row.price,
                id: 'price',
                footer: (props) => props.column.id,
            },
            {
                header: "Sales Price",
                accessorFn: row => row.total_price,
                id: 'total_price',
                footer: (props) => props.column.id,
            },
            {
                header: "DISCOUNT %",
                accessorFn: row => row.discount,
                id: 'discount',
                footer: (props) => props.column.id,
            },
            {
                header: "LINK",
                accessorFn: row => row.offer_link,
                id: 'offer_link',
                footer: (props) => props.column.id,
            },
        ],
        []
    );
    const [data, setData] = useState([]);
    async function getData () {
        const resp = await axios.post('/getTopAmazon', {});
        if(resp.status == 200) {
            setData(resp.data.product);
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
        debugTable: true,
    });
    return (
        <div className="p-2">
            <div className="h-2" />
            <div className="mb-3 text-lg font-bold"><h1>Top Discount</h1></div>
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
