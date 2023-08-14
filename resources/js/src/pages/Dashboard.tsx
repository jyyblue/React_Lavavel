import React from 'react';
import CardFour from '../component/CardFour';
import CardOne from '../component/CardOne';
import CardThree from '../component/CardThree';
import CardTwo from '../component/CardTwo';
import ChartOne from '../component/ChartOne';
import ChartThree from '../component/ChartThree';
import ChartTwo from '../component/ChartTwo';
import MapOne from '../component/MapOne';
import TableOne from '../component/TableOne';

const Dashboard = () => {
  return (
    <>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
        <CardOne />
        <CardTwo />
        <CardThree />
        <CardFour />
      </div>

      <div className="mt-4 grid grid-cols-12 gap-4 md:mt-6 md:gap-6 2xl:mt-7.5 2xl:gap-7.5">
        <ChartOne />
        <ChartTwo />
        <ChartThree />
        <MapOne />
        <div className="col-span-12 xl:col-span-8">
          <TableOne />
        </div>
      </div>
    </>
  );
};

export default Dashboard;
