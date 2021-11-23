

CREATE OR REPLACE VIEW `View1_BuyCoins` as
SELECT `Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`, `Cn`.`DoNotBuy`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
, `Cmc`.`ID` as `IDCmc`, `Cmc`.`CoinID` as `CoinID2`, `Cmc`.`LiveMarketCap`, `Cmc`.`LastMarketCap`, ((`Cmc`.`LiveMarketCap`-`Cmc`.`LastMarketCap`)/`Cmc`.`LastMarketCap`)*100 as `MarketCapPctChange`
, `Cbo`.`ID` as `IDCbo`, `Cbo`.`CoinID` as `CoinID3`, `Cbo`.`LiveBuyOrders`, `Cbo`.`LastBuyOrders`, ((`Cbo`.`LiveBuyOrders`-`Cbo`.`LastBuyOrders`)/`Cbo`.`LastBuyOrders`)* 100 as `BuyOrdersPctChange`
, `Cv`.`ID` as `IDCv`, `Cv`.`CoinID` as `CoinID4`, `Cv`.`LiveVolume`, `Cv`.`LastVolume`, (( `Cv`.`LiveVolume`- `Cv`.`LastVolume`)/ `Cv`.`LastVolume`)*100 as `VolumePctChange`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`, ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
,`Cso`.`ID`as `IDCso`, `Cso`.`CoinID`, `Cso`.`LiveSellOrders`, `Cso`.`LastSellOrders`,((`Cso`.`LiveSellOrders`-`Cso`.`LastSellOrders`)/`Cso`.`LastSellOrders`)*100 as `SellOrdersPctChange`
,if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` > 0, 1, if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` < 0, -1, 0)) as  `LivePriceTrend`
          ,if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` > 0, 1, if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` < 0, -1, 0)) as  `LastPriceTrend`
          ,if(`Cp`.`Price3` -`Cp`.`Price4` > 0, 1, if(`Cp`.`Price3` -`Cp`.`Price4` < 0, -1, 0)) as  `Price3Trend`
          ,if(`Cp`.`Price4` -`Cp`.`Price5` > 0, 1, if(`Cp`.`Price4` -`Cp`.`Price5` < 0, -1, 0)) as  `Price4Trend`
,if(`Cpc`.`Live1HrChange`-`Last1HrChange` >0,1,if(`Cpc`.`Live1HrChange`-`Last1HrChange` <0,-1,0)) as `1HrPriceChangeLive`
,if(`Last1HrChange`-`1HrChange3`>0,1,if(`Last1HrChange`-`1HrChange3`<0,-1,0)) as `1HrPriceChangeLast`
,if(`1HrChange3`-`1HrChange4`>0,1,if(`1HrChange3`-`1HrChange4`<0,-1,0)) as `1HrPriceChange3`
,if(`1HrChange4`-`1HrChange5`>0,1,if(`1HrChange4`-`1HrChange5`<0,-1,0)) as `1HrPriceChange4`
FROM `Coin` `Cn`
join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Cn`.`ID`
join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Cn`.`ID`
join `CoinBuyOrders` `Cbo` on `Cbo`.`CoinID` = `Cn`.`ID`
join `CoinVolume` `Cv` on `Cv`.`CoinID` = `Cn`.`ID`
join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cn`.`ID`
join `CoinSellOrders` `Cso` on `Cso`.`CoinID` = `Cn`.`ID`
where `Cn`.`BuyCoin` = 1 and `Cn`.`DoNotBuy` = 0;


CREATE OR REPLACE VIEW `View2_TrackingBuyCoins` as
