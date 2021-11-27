

CREATE OR REPLACE VIEW `View1_BuyCoins` as
SELECT `Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
, `Cmc`.`ID` as `IDCmc`, `Cmc`.`CoinID` as `CoinID2`, `Cmc`.`LiveMarketCap`, `Cmc`.`LastMarketCap`, ((`Cmc`.`LiveMarketCap`-`Cmc`.`LastMarketCap`)/`Cmc`.`LastMarketCap`)*100 as `MarketCapPctChange`
, `Cbo`.`ID` as `IDCbo`, `Cbo`.`CoinID` as `CoinID3`, `Cbo`.`LiveBuyOrders`, `Cbo`.`LastBuyOrders`, ((`Cbo`.`LiveBuyOrders`-`Cbo`.`LastBuyOrders`)/`Cbo`.`LastBuyOrders`)* 100 as `BuyOrdersPctChange`
, `Cv`.`ID` as `IDCv`, `Cv`.`CoinID` as `CoinID4`, `Cv`.`LiveVolume`, `Cv`.`LastVolume`, (( `Cv`.`LiveVolume`- `Cv`.`LastVolume`)/ `Cv`.`LastVolume`)*100 as `VolumePctChange`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`
, ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
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
SELECT `Tc`.`ID` as `IDTc`, `Tc`.`CoinID`, `Tc`.`CoinPrice`, `Tc`.`TrackDate`, `Tc`.`UserID`, `Tc`.`BaseCurrency` as `TrackingBaseCurrency`, `Tc`.`SendEmail`, `Tc`.`BuyCoin`, `Tc`.`Quantity`
, `Tc`.`RuleIDBuy`, `Tc`.`CoinSellOffsetPct`, `Tc`.`CoinSellOffsetEnabled`, `Tc`.`BuyType`, `Tc`.`MinsToCancelBuy`, `Tc`.`SellRuleFixed`, `Tc`.`Status` as `TrackingStatus`, `Tc`.`ToMerge`
, `Tc`.`NoOfPurchases`, `Tc`.`NoOfRisesInPrice`, `Tc`.`OriginalPrice`, `Tc`.`BuyRisesInPrice`, `Tc`.`TransactionID`, `Tc`.`Type` as `TrackingType`, `Tc`.`LastPrice`, `Tc`.`SBRuleID`
, `Tc`.`SBTransID`, `Tc`.`quickBuyCount`, `Tc`.`OverrideCoinAllocation`, `Tc`.`CoinSwapID`, `Tc`.`OldBuyBackTransID`, `Tc`.`BaseBuyPrice`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
, `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
, `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
, `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
, `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
, `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
, `Uc`.`TotalPurchasesPerCoin`
,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
, `Us`.`DisableUntil`
,`Br`.`ID` as `IDBr`, `RuleName`, `Br`.`UserID` as `UserID3`, `Br`.`BuyOrdersEnabled`, `Br`.`BuyOrdersTop`, `Br`.`BuyOrdersBtm`, `Br`.`MarketCapEnabled`, `Br`.`MarketCapTop`, `Br`.`MarketCapBtm`, `Br`.`1HrChangeEnabled`
, `Br`.`1HrChangeTop`, `Br`.`1HrChangeBtm`, `Br`.`24HrChangeEnabled`, `Br`.`24HrChangeTop`, `Br`.`24HrChangeBtm`, `Br`.`7DChangeEnabled`, `Br`.`7DChangeTop`, `Br`.`7DChangeBtm`, `Br`.`CoinPriceEnabled`
, `Br`.`CoinPriceTop`, `Br`.`CoinPriceBtm`, `Br`.`SellOrdersEnabled`, `Br`.`SellOrdersTop`, `Br`.`SellOrdersBtm`, `Br`.`VolumeEnabled`, `Br`.`VolumeTop`, `Br`.`VolumeBtm`, `Br`.`BuyCoin`as `BuyCoin3`, `Br`.`SendEmail` as `SendEmail2`
, `Br`.`BTCAmount`, `Br`.`BuyType` as `BuyType2`, `Br`.`CoinOrder`, `Br`.`BuyCoinOffsetEnabled`, `Br`.`BuyCoinOffsetPct`, `Br`.`PriceTrendEnabled`, `Br`.`Price4Trend`, `Br`.`Price3Trend`, `Br`.`LastPriceTrend`
, `Br`.`LivePriceTrend`, `Br`.`BuyPriceMinEnabled`, `Br`.`BuyPriceMin`, `Br`.`LimitToCoin`, `Br`.`LimitToCoinID`, `Br`.`AutoBuyCoinEnabled`, `Br`.`AutoBuyCoinPct`, `Br`.`BuyAmountOverrideEnabled`
, `Br`.`BuyAmountOverride`, `Br`.`NewBuyPattern`, `Br`.`SellRuleFixed` as `SellRuleFixed2`, `Br`.`OverrideDailyLimit`, `Br`.`CoinPricePatternEnabled`, `Br`.`CoinPricePattern`, `Br`.`1HrChangeTrendEnabled`, `Br`.`1HrChangeTrend`
, `Br`.`CoinPriceMatchPattern`, `Br`.`CoinPriceMatchID`, `Br`.`CoinPricePatternID`, `Br`.`Coin1HrPatternID`, `Br`.`BuyRisesInPrice` as `BuyRisesInPrice2`, `Br`.`DisableUntil` as `DisableUntil2`, `Br`.`OverrideDisableRule`, `Br`.`LimitBuyAmountEnabled`
, `Br`.`LimitBuyAmount`, `Br`.`OverrideCancelBuyTimeEnabled`, `Br`.`OverrideCancelBuyTimeMins`, `Br`.`LimitBuyTransactionsEnabled`, `Br`.`LimitBuyTransactions`, `Br`.`NoOfBuyModeOverrides`
, `Br`.`CoinModeOverridePriceEnabled`, `Br`.`BuyModeActivate`, `Br`.`CoinMode`, `Br`.`OverrideCoinAllocation` as `OverrideCoinAllocation2`, `Br`.`OneTimeBuyRule`, `Br`.`LimitToBaseCurrency`, `Br`.`EnableRuleActivationAfterDip`
, `Br`.`24HrPriceDipPct`, `Br`.`7DPriceDipPct`, `Br`.`BuyAmountCalculationEnabled`, `Br`.`TotalPurchasesPerRule`
,`Athl`.`ID` as `IDAthl`, `Athl`.`CoinID` as `CoinID3`, `Athl`.`HighLow`, MAX(`Athl`.`Price`) as `ATHPrice`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`
, ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
FROM `TrackingCoins` `Tc`
join `CoinPrice` `Cp` on `Cp`.`CoinID` =   `Tc`.`CoinID`
join `Coin` `Cn` on `Cn`.`ID` = `Cp`.`CoinID`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Tc`.`UserID`
join `User` `Us` on `Us`.`ID` = `Tc`.`UserID`
join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cn`.`ID`
Left join `BuyRules` `Br` on `Br`.`ID` = `Tc`.`RuleIDBuy`
left join `AllTimeHighLow` `Athl` on `Athl`.`CoinID` = `Tc`.`CoinID` and `HighLow` = 'High';


CREATE OR REPLACE VIEW `View3_SpreadBetBuy` as
;

CREATE OR REPLACE VIEW `View4_BittrexBuySell` as
SELECT `Ba`.`ID` as `IDBa`, `Ba`.`CoinID`as `CoinID4`, `Ba`.`TransactionID`, `Ba`.`UserID` AS `UserIDBa`, `Ba`.`Type` as `TypeBa`, `Ba`.`BittrexRef` as `BittrexRefBa`, `Ba`.`ActionDate`, `Ba`.`CompletionDate` as `CompletionDateBa`, `Ba`.`Status` as `StatusBa`, `Ba`.`SellPrice`, `Ba`.`RuleID`, `Ba`.`RuleIDSell`, `Ba`.`QuantityFilled`, `Ba`.`MultiplierPrice`, `Ba`.`BuyBack`, `Ba`.`OldBuyBackTransID`, `Ba`.`ResidualAmount`
,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
, `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
, `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
, `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
, `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
, `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
, `Uc`.`TotalPurchasesPerCoin`
,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
, `Us`.`DisableUntil`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
,`Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID3`,`Tr`.`UserID` AS `UserID3`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `Status`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRef`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`
, ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
,`Sr`.`ID` as `IDSr`, `Sr`.`RuleName`, `Sr`.`UserID` as `UserID`, `Sr`.`SellCoin`, `Sr`.`SendEmail`, `Sr`.`BuyOrdersEnabled`, `Sr`.`BuyOrdersTop`, `Sr`.`BuyOrdersBtm`, `Sr`.`MarketCapEnabled`, `Sr`.`MarketCapTop`, `Sr`.`MarketCapBtm`, `Sr`.`1HrChangeEnabled`, `Sr`.`1HrChangeTop`, `Sr`.`1HrChangeBtm`, `Sr`.`24HrChangeEnabled`, `Sr`.`24HrChangeTop`, `Sr`.`24HrChangeBtm`, `Sr`.`7DChangeEnabled`, `Sr`.`7DChangeTop`, `Sr`.`7DChangeBtm`, `Sr`.`ProfitPctEnabled`, `Sr`.`ProfitPctTop`, `Sr`.`ProfitPctBtm`, `Sr`.`CoinPriceEnabled`, `Sr`.`CoinPriceTop`, `Sr`.`CoinPriceBtm`, `Sr`.`SellOrdersEnabled`, `Sr`.`SellOrdersTop`, `Sr`.`SellOrdersBtm`, `Sr`.`VolumeEnabled`, `Sr`.`VolumeTop`, `Sr`.`VolumeBtm`, `Sr`.`CoinOrder`, `Sr`.`SellCoinOffsetEnabled`, `Sr`.`SellCoinOffsetPct`, `Sr`.`SellPriceMinEnabled`, `Sr`.`SellPriceMin`, `Sr`.`LimitToCoin`, `Sr`.`LimitToCoinID`, `Sr`.`AutoSellCoinEnabled`, `Sr`.`AutoSellCoinPct`, `Sr`.`SellPatternEnabled`, `Sr`.`SellPattern`, `Sr`.`LimitToBuyRule`, `Sr`.`CoinPricePatternEnabled`, `Sr`.`CoinPricePattern`, `Sr`.`CoinPriceMatchNameID`, `Sr`.`CoinPricePatternNameID`, `Sr`.`CoinPrice1HrPatternNameID`, `Sr`.`SellFallsInPrice`, `Sr`.`CoinModeRule`, `Sr`.`CoinSwapEnabled`, `Sr`.`CoinSwapAmount`, `Sr`.`NoOfCoinSwapsPerWeek`, `Sr`.`MergeCoinEnabled`
,DateDiff(`Ba`.`ActionDate`,now()) as `DaysOutstanding`
,DateDiff(`Ba`.`ActionDate`,now()) as `timeSinceAction`
,DateDiff(`Ba`.`ActionDate`,now()) as `MinsSinceAction`
FROM `BittrexAction`  `Ba`
 join `User` `Us` on `Us`.`ID` = `Ba`.`UserID`
    join `UserConfig` `Uc` on `Uc`.`UserID` = `Ba`.`UserID`
    join `Coin` `Cn` on `Cn`.`ID` = `Ba`.`CoinID`
    join `CoinPrice` `Cp` on `Ba`.`CoinID` = `Cp`.`CoinID`
    join `Transaction` `Tr` on (`Tr`.`ID` = `Ba`.`TransactionID`) and (`Tr`.`Type` = `Ba`.`Type`)
    join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Ba`.`CoinID`
    left join `SellRules` `Sr` on `Sr`.`ID` = `Tr`.`FixSellRule`;

    CREATE OR REPLACE VIEW `View5_SellCoins` as
    select `Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `Status`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRef`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`,`Cn`.`ID` AS `IDCn`,`Cn`.`Symbol` AS `Symbol`,`Cn`.`Name` AS `Name`,`Cn`.`BaseCurrency` AS `BaseCurrency`,`Cn`.`BuyCoin` AS `BuyCoin`,`Cn`.`CMCID` AS `CMCID`,`Cn`.`SecondstoUpdate` AS `SecondstoUpdate`,`Cn`.`Image` AS `Image`,`Cn`.`MinTradeSize` AS `MinTradeSize`,`Cn`.`CoinPrecision` AS `CoinPrecision`,`Cn`.`DoNotBuy` AS `DoNotBuy`,`Cp`.`ID` AS `IDCp`,`Cp`.`CoinID` AS `CoinID6`,`Cp`.`LiveCoinPrice` AS `LiveCoinPrice`,`Cp`.`LastCoinPrice` AS `LastCoinPrice`,`Cp`.`Price3` AS `Price3`,`Cp`.`Price4` AS `Price4`,`Cp`.`Price5` AS `Price5`,`Cp`.`LastUpdated` AS `LastUpdated`,(((`Cp`.`LiveCoinPrice` - `Cp`.`LastCoinPrice`) / `Cp`.`LastCoinPrice`) * 100) AS `CoinPricePctChange`,`Cmc`.`ID` AS `IDCmc`,`Cmc`.`CoinID` AS `CoinID2`,`Cmc`.`LiveMarketCap` AS `LiveMarketCap`,`Cmc`.`LastMarketCap` AS `LastMarketCap`,(((`Cmc`.`LiveMarketCap` - `Cmc`.`LastMarketCap`) / `Cmc`.`LastMarketCap`) * 100) AS `MarketCapPctChange`,`Cbo`.`ID` AS `IDCbo`,`Cbo`.`CoinID` AS `CoinID3`,`Cbo`.`LiveBuyOrders` AS `LiveBuyOrders`,`Cbo`.`LastBuyOrders` AS `LastBuyOrders`,(((`Cbo`.`LiveBuyOrders` - `Cbo`.`LastBuyOrders`) / `Cbo`.`LastBuyOrders`) * 100) AS `BuyOrdersPctChange`,`Cv`.`ID` AS `IDCv`,`Cv`.`CoinID` AS `CoinID4`,`Cv`.`LiveVolume` AS `LiveVolume`,`Cv`.`LastVolume` AS `LastVolume`,(((`Cv`.`LiveVolume` - `Cv`.`LastVolume`) / `Cv`.`LastVolume`) * 100) AS `VolumePctChange`,`Cpc`.`ID` AS `IDCpc`,`Cpc`.`CoinID` AS `CoinID5`,`Cpc`.`Live1HrChange` AS `Live1HrChange`,`Cpc`.`Last1HrChange` AS `Last1HrChange`,`Cpc`.`Live24HrChange` AS `Live24HrChange`,`Cpc`.`Last24HrChange` AS `Last24HrChange`,`Cpc`.`Live7DChange` AS `Live7DChange`,`Cpc`.`Last7DChange` AS `Last7DChange`,`Cpc`.`1HrChange3` AS `1HrChange3`,`Cpc`.`1HrChange4` AS `1HrChange4`,`Cpc`.`1HrChange5` AS `1HrChange5`,(((`Cpc`.`Live1HrChange` - `Cpc`.`Last1HrChange`) / `Cpc`.`Last1HrChange`) * 100) AS `Hr1ChangePctChange`,(((`Cpc`.`Last24HrChange` - `Cpc`.`Last24HrChange`) / `Cpc`.`Last24HrChange`) * 100) AS `Hr24ChangePctChange`,(((`Cpc`.`Live7DChange` - `Cpc`.`Last7DChange`) / `Cpc`.`Last7DChange`) * 100) AS `D7ChangePctChange`,`Cso`.`ID` AS `IDCso`,`Cso`.`CoinID` AS `CoinID7`,`Cso`.`LiveSellOrders` AS `LiveSellOrders`,`Cso`.`LastSellOrders` AS `LastSellOrders`,(((`Cso`.`LiveSellOrders` - `Cso`.`LastSellOrders`) / `Cso`.`LastSellOrders`) * 100) AS `SellOrdersPctChange`,if(((`Cp`.`LiveCoinPrice` - `Cp`.`LastCoinPrice`) > 0),1,if(((`Cp`.`LiveCoinPrice` - `Cp`.`LastCoinPrice`) < 0),-(1),0)) AS `LivePriceTrend`,if(((`Cp`.`LastCoinPrice` - `Cp`.`Price3`) > 0),1,if(((`Cp`.`LastCoinPrice` - `Cp`.`Price3`) < 0),-(1),0)) AS `LastPriceTrend`,if(((`Cp`.`Price3` - `Cp`.`Price4`) > 0),1,if(((`Cp`.`Price3` - `Cp`.`Price4`) < 0),-(1),0)) AS `Price3Trend`,if(((`Cp`.`Price4` - `Cp`.`Price5`) > 0),1,if(((`Cp`.`Price4` - `Cp`.`Price5`) < 0),-(1),0)) AS `Price4Trend`,if(((`Cpc`.`Live1HrChange` - `Cpc`.`Last1HrChange`) > 0),1,if(((`Cpc`.`Live1HrChange` - `Cpc`.`Last1HrChange`) < 0),-(1),0)) AS `1HrPriceChangeLive`,if(((`Cpc`.`Last1HrChange` - `Cpc`.`1HrChange3`) > 0),1,if(((`Cpc`.`Last1HrChange` - `Cpc`.`1HrChange3`) < 0),-(1),0)) AS `1HrPriceChangeLast`,if(((`Cpc`.`1HrChange3` - `Cpc`.`1HrChange4`) > 0),1,if(((`Cpc`.`1HrChange3` - `Cpc`.`1HrChange4`) < 0),-(1),0)) AS `1HrPriceChange3`,if(((`Cpc`.`1HrChange4` - `Cpc`.`1HrChange5`) > 0),1,if(((`Cpc`.`1HrChange4` - `Cpc`.`1HrChange5`) < 0),-(1),0)) AS `1HrPriceChange4`,`Uc`.`UserID` AS `UserID2`,`Uc`.`APIKey` AS `APIKey`,`Uc`.`APISecret` AS `APISecret`,`Uc`.`EnableDailyBTCLimit` AS `EnableDailyBTCLimit`,`Uc`.`EnableTotalBTCLimit` AS `EnableTotalBTCLimit`,`Uc`.`DailyBTCLimit` AS `DailyBTCLimit`,`Uc`.`TotalBTCLimit` AS `TotalBTCLimit`,`Uc`.`BTCBuyAmount` AS `BTCBuyAmount`,`Uc`.`CoinSellOffsetEnabled` AS `CoinSellOffsetEnabled2`,`Uc`.`CoinSellOffsetPct` AS `CoinSellOffsetPct2`,`Uc`.`BaseCurrency` AS `BaseCurrency2`,`Uc`.`NoOfCoinPurchase` AS `NoOfCoinPurchase`,`Uc`.`TimetoCancelBuy` AS `TimetoCancelBuy`,`Uc`.`TimeToCancelBuyMins` AS `TimeToCancelBuyMins`,`Uc`.`KEK` AS `KEK`,`Uc`.`MinsToPauseAlert` AS `MinsToPauseAlert`,`Uc`.`LowPricePurchaseEnabled` AS `LowPricePurchaseEnabled`,`Uc`.`NoOfPurchases` AS `NoOfPurchases2`,`Uc`.`PctToPurchase` AS `PctToPurchase`,`Uc`.`TotalRisesInPrice` AS `TotalRisesInPrice`,`Uc`.`TotalRisesInPriceSell` AS `TotalRisesInPriceSell`,`Uc`.`ReservedUSDT` AS `ReservedUSDT`,`Uc`.`ReservedBTC` AS `ReservedBTC`,`Uc`.`ReservedETH` AS `ReservedETH`,`Uc`.`TotalProfitPauseEnabled` AS `TotalProfitPauseEnabled`,`Uc`.`TotalProfitPause` AS `TotalProfitPause`,`Uc`.`PauseRulesEnabled` AS `PauseRulesEnabled`,`Uc`.`PauseRules` AS `PauseRules`,`Uc`.`PauseHours` AS `PauseHours`,`Uc`.`MergeAllCoinsDaily` AS `MergeAllCoinsDaily`,`Uc`.`MarketDropStopEnabled` AS `MarketDropStopEnabled`,`Uc`.`MarketDropStopPct` AS `MarketDropStopPct`,`Uc`.`SellAllCoinsEnabled` AS `SellAllCoinsEnabled`,`Uc`.`SellAllCoinsPct` AS `SellAllCoinsPct`,`Uc`.`CoinModeEmails` AS `CoinModeEmails`,`Uc`.`CoinModeEmailsSell` AS `CoinModeEmailsSell`,`Uc`.`CoinModeMinsToCancelBuy` AS `CoinModeMinsToCancelBuy`,`Uc`.`PctToSave` AS `PctToSave`,`Uc`.`SplitBuyAmounByPctEnabled` AS `SplitBuyAmounByPctEnabled`,`Uc`.`NoOfSplits` AS `NoOfSplits`,`Uc`.`SaveResidualCoins` AS `SaveResidualCoins`,`Uc`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Uc`.`SpreadBetRuleID` AS `SpreadBetRuleID2`,`Uc`.`MinsToPauseAfterPurchase` AS `MinsToPauseAfterPurchase`,`Uc`.`LowMarketModeEnabled` AS `LowMarketModeEnabled`,`Uc`.`LowMarketModeDate` AS `LowMarketModeDate`,`Uc`.`AutoMergeSavings` AS `AutoMergeSavings`,`Uc`.`AllBuyBackAsOverride` AS `AllBuyBackAsOverride`,`Uc`.`TotalPurchasesPerCoin` AS `TotalPurchasesPerCoin`
    ,`Tr`.`CoinPrice`*`Tr`.`Amount` as OriginalPrice,
      ((`Tr`.`CoinPrice`*`Tr`.`Amount`)/100)*0.28 as CoinFee,
      `Cp`.`LiveCoinPrice`*`Tr`.`Amount` as LivePrice
      ,(`Cp`.`LiveCoinPrice`*`Tr`.`Amount`)-(`Tr`.`CoinPrice`*`Tr`.`Amount`)-(((`Tr`.`CoinPrice`*`Tr`.`Amount`)/100)*0.28) as ProfitUSD
      ,(((`Cp`.`LiveCoinPrice`*`Tr`.`Amount`)-(`Tr`.`CoinPrice`*`Tr`.`Amount`)-(((`Tr`.`CoinPrice`*`Tr`.`Amount`)/100)*0.28)) /(`Tr`.`CoinPrice`*`Tr`.`Amount`))*100 as ProfitPct
      ,TimeStampDiff(MINUTE, `Tr`.`DelayCoinSwapUntil`, now()) as `minsToDelay`
      ,TIMESTAMPDIFF(MINUTE, `Tr`.`OrderDate`, Now()) as MinsFromBuy
      ,`Sbr`.`ID` as `IDSbr`, `Sbr`.`Name` as `SpreadBetRuleName`, `Sbr`.`UserID` as `UserIDSbr`
      ,`Bi`.`ID` as `IDBi`, `Bi`.`CoinID` as `CoinIDBi`, `Bi`.`TopPrice`, `Bi`.`LowPrice`, `Bi`.`Difference`, `Bi`.`NoOfSells`
      ,`Rls`.`ID` as `IDRls`, `Rls`.`UserID` as `UserIDRls` , `Rls`.`Enabled`, `Rls`.`SellPct`, `Rls`.`OriginalPriceMultiplier`
    from ((((((((`Transaction` `Tr`
      join `Coin` `Cn` on((`Cn`.`ID` = `Tr`.`CoinID`)))
      join `CoinPrice` `Cp` on((`Cp`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinBuyOrders` `Cbo` on((`Cbo`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinMarketCap` `Cmc` on((`Cmc`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinSellOrders` `Cso` on((`Cso`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinVolume` `Cv` on((`Cv`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinPctChange` `Cpc` on((`Cpc`.`CoinID` = `Tr`.`CoinID`)))
      join `UserConfig` `Uc` on((`Uc`.`UserID` = `Tr`.`UserID`)))
      join `SpreadBetRules` `Sbr` on `Sbr`.`ID` = `Tr`.`SpreadBetRuleID`
      join `BounceIndex` `Bi` on `Bi`.`CoinID` = `Tr`.`CoinID`
      Left Join `ReduceLossSettings` `Rls` on `Rls`.`UserID` = `Tr`.`UserID`;

CREATE OR REPLACE VIEW `View6_TrackingSellCoins` as
SELECT `Tsc`.`ID` as `IDTsc`, `Tsc`.`CoinPrice` as `CoinPriceTsc`, `Tsc`.`TrackDate`, `Tsc`.`UserID` as `UserIDTsc`, `Tsc`.`NoOfRisesInPrice`, `Tsc`.`TransactionID` as `TransactionIDTsc`, `Tsc`.`Status` as `StatusTsc`, `Tsc`.`SellCoin`, `Tsc`.`SendEmail`, `Tsc`.`CoinSellOffsetEnabled`, `Tsc`.`CoinSellOffsetPct`, `Tsc`.`TrackStartDate`, `Tsc`.`SellFallsInPrice`, `Tsc`.`BaseSellPrice`, `Tsc`.`LastPrice`, `Tsc`.`Type` as `TrackingType`, `Tsc`.`OriginalSellPrice`
,`Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `Status`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRef`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
, `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
, `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
, `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
, `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
, `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
, `Uc`.`TotalPurchasesPerCoin`
,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
, `Us`.`DisableUntil`
,TIMESTAMPDIFF(MINUTE,`TrackDate`, Now()) as MinsFromDate,(`LiveCoinPrice`*`Amount`)-(`Tr`.`CoinPrice`*`Amount`) as `ProfitUSD`, ((`LiveCoinPrice`*`Amount`)/100)*0.28 as `Fee`
        ,(((`LiveCoinPrice`*`Amount`)-(`Tr`.`CoinPrice`*`Amount`))/(`Tr`.`CoinPrice`*`Amount`))*100`PctProfit`
 , ((`LiveCoinPrice`*`Amount`)-(`Tr`.`CoinPrice` * `Amount`))/ (`Tr`.`CoinPrice` * `Amount`) * 100 as `OgPctProfit`
, `Tr`.`CoinPrice` * `Amount` as `OriginalPurchasePrice`
   ,TIMESTAMPDIFF(MINUTE,`TrackStartDate`,Now()) as MinsFromStart
,(`LiveCoinPrice`*`Amount`) as LiveTotalPrice
FROM `TrackingSellCoins` `Tsc`
Join `Transaction` `Tr` on `Tr`.`ID` = `Tsc`.`TransactionID`
 Join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
 join `UserConfig` `Uc` on `Uc`.`UserID` = `Tr`.`UserID`
 join `User` `Us` on `Us`.`ID` = `Tr`.`UserID`
 join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`;


 CREATE OR REPLACE VIEW `View7_SpreadBetSell` as
 SELECT `Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `Status`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRef`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`
 ,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
 , `Cn`.`DoNotBuy`
 , `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
 , `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`
 , `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
 ,`Sbs`. `ID`, `Sbs`. `SpreadBetRuleID` as `SpreadBetRuleIDSbs`, `Sbs`. `Hr1BuyPrice`, `Sbs`. `Hr24BuyPrice`, `Sbs`. `D7BuyPrice`, `Sbs`. `NextReviewDate`, `Sbs`. `PctProfitSell`, `Sbs`. `NoOfTransactions`, `Sbs`. `LowestPctProfit`, `Sbs`. `AvgTimeToSell`, `Sbs`. `Enabled`, `Sbs`. `Hr1BuyEnable`, `Sbs`. `Hr1BuyDisable`, `Sbs`. `Hr24andD7StartPrice`, `Sbs`. `Month6TotalPrice`, `Sbs`. `AllTimeTotalPrice`, `Sbs`. `Hr1EnableStartPrice`, `Sbs`. `BuyFallsinPrice`, `Sbs`. `SellRaisesInPrice`, `Sbs`. `MinsToCancel`, `Sbs`. `CalculatedMinsToCancel`, `Sbs`. `CalculatedRisesInPrice`, `Sbs`. `CalculatedFallsinPrice`, `Sbs`. `AutoBuyBackSell`
 ,`Athl`. `ID` as `IDAthl`, `Athl`. `CoinID` as `CoinIDAthl`, `Athl`. `HighLow`, `Athl`. `Price`
 ,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
 , `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
 , `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
 , `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
 , `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
 , `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
 , `Uc`.`TotalPurchasesPerCoin`
 ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
 , `Us`.`DisableUntil`
 ,`Sbt`. `ID` as `IDSbt`, `Sbt`. `SpreadBetRuleID` as `SpreadBetRuleIDSbt`, `Sbt`. `TotalAmountToBuy`, `Sbt`. `AmountPerCoin`
 , `Cbo`.`ID` as `IDCbo`, `Cbo`.`CoinID` as `CoinID3`, `Cbo`.`LiveBuyOrders`, `Cbo`.`LastBuyOrders`
 , `Cmc`.`ID` as `IDCmc`, `Cmc`.`CoinID` as `CoinIDCmc`, `Cmc`.`LiveMarketCap`, `Cmc`.`LastMarketCap`
 ,`Cso`.`ID` AS `IDCso`,`Cso`.`CoinID` AS `CoinID7`,`Cso`.`LiveSellOrders` AS `LiveSellOrders`,`Cso`.`LastSellOrders` AS `LastSellOrders`
 , `Cv`.`ID` as `IDCv`, `Cv`.`CoinID` as `CoinID4`, `Cv`.`LiveVolume`, `Cv`.`LastVolume`
 ,`Bi`.`ID` as `IDBi`, `Bi`.`CoinID` as `CoinIDBi`, `Bi`.`TopPrice`, `Bi`.`LowPrice`, `Bi`.`Difference`, `Bi`.`NoOfSells`
 ,`Sbr`.`ID` as `IDSbr`, `Sbr`.`Name` as `SpreadBetRuleName`, `Sbr`.`UserID` as `UserIDSbr`
 ,if(`Cn`.`BaseCurrency` = 'BTC',getBTCPrice(84),if(`Cn`.`BaseCurrency` = 'ETH',getBTCPrice(85),1.0)) as BaseMultiplier
     FROM `Transaction` `Tr`
     join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
     join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Tr`.`CoinID`
     join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
     join `SpreadBetSettings` `Sbs` on `Sbs`.`SpreadBetRuleID` = `Tr`.`SpreadBetRuleID`
     Join `AllTimeHighLow` `Athl` on `Athl`.`CoinID` = `Tr`.`CoinID` and `HighLow` = 'High'
     join `UserConfig` `Uc` on `Uc`.`UserID` = `Tr`.`UserID`
     join `User` `Us` on `Us`.`ID` = `Tr`.`UserID`
     join `SpreadBetTransactions` `Sbt` on `Sbt`.`SpreadBetRuleID` = `Tr`.`SpreadBetRuleID`
     join `CoinBuyOrders` `Cbo` on `Cbo`.`CoinID` = `Cn`.`ID`
     join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Cn`.`ID`
     join `CoinSellOrders` `Cso` on `Cso`.`CoinID` = `Tr`.`CoinID`
     join `CoinVolume` `Cv` on `Cv`.`CoinID` = `Cn`.`ID`
     join `BounceIndex` `Bi` on `Bi`.`CoinID` = `Tr`.`CoinID`
      join `SpreadBetRules` `Sbr` on `Sbr`.`ID` = `Tr`.`SpreadBetRuleID`;

 CREATE OR REPLACE VIEW `View8_SwapCoin` as
 SELECT `Sc`.`ID` as `IDSc`, `Sc`.`TransactionID` as `TransactionIDSc`, `Sc`.`Status`, `Sc`.`BittrexRef`, `Sc`.`NewCoinIDCandidate`, `Sc`.`NewCoinPrice`, `Sc`.`BaseCurrency` as `BaseCurrencySc`, `Sc`.`TotalAmount`, `Sc`.`OriginalPurchaseAmount`, `Sc`.`BittrexRefSell`, `Sc`.`SellFinalPrice`, `Sc`.`PctToBuy`
 ,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
 , `Cn`.`DoNotBuy`
 , `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
 ,`Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `StatusTr`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRefTr`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`
 ,`Cn2`.`Symbol` as `OriginalSymbol`
 ,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
 , `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
 , `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
 , `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
 , `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
 , `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
 , `Uc`.`TotalPurchasesPerCoin`
 ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
 , `Us`.`DisableUntil`
 , `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
 , `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`
 FROM `SwapCoins` `Sc`
 join `Coin` `Cn` on `Cn`.`ID` = `Sc`.`NewCoinIDCandidate`
 join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Sc`.`NewCoinIDCandidate`
 join `Transaction` `Tr` on `Tr`.`ID` = `Sc`.`TransactionID`
 join `Coin` `Cn2` on `Cn2`.`ID` = `Tr`.`CoinID`
 join `UserConfig` `Uc` on `Uc`.`UserID` = `Tr`.`UserID`
 join `User` `Us` on `Us`.`ID` = `Tr`.`UserID`
 join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Sc`.`NewCoinIDCandidate`;

CREATE OR REPLACE VIEW `View9_BuyBack` as
SELECT `Bb`.`ID`, `Bb`.`TransactionID`, `Bb`.`Quantity`, `Bb`.`SellPrice`, `Bb`.`Status`, `Bb`.`ProfitMultiply`, `Bb`.`NoOfRaisesInPrice`, `Bb`.`BuyBackPct`, `Bb`.`MinsToCancel`, `Bb`.`DateTimeAdded`, `Bb`.`DelayTime`
,`Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `StatusTr`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRefTr`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`
,`Ba`.`ID` as `IDBa`, `Ba`.`CoinID`as `CoinID4`, `Ba`.`TransactionID`, `Ba`.`UserID` AS `UserIDBa`, `Ba`.`Type` as `TypeBa`, `Ba`.`BittrexRef` as `BittrexRefBa`, `Ba`.`ActionDate`, `Ba`.`CompletionDate` as `CompletionDateBa`, `Ba`.`Status` as `StatusBa`, `Ba`.`SellPrice`, `Ba`.`RuleID`, `Ba`.`RuleIDSell`, `Ba`.`QuantityFilled`, `Ba`.`MultiplierPrice`, `Ba`.`BuyBack`, `Ba`.`OldBuyBackTransID`, `Ba`.`ResidualAmount`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
, `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
, `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
, `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
, `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
, `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
, `Uc`.`TotalPurchasesPerCoin`
,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
, `Us`.`DisableUntil`
,`Bbs`.`ID`, `Bbs`.`CoinID`, `Bbs`.`LastPriceChange`, `Bbs`.`Min15PriceChange`, `Bbs`.`Min30PriceChange`, `Bbs`.`Min45PriceChange`, `Bbs`.`Min75PriceChange`, `Bbs`.`OneHrPriceChange`, `Bbs`.`Twenty4HrPriceChange`, `Bbs`.`MarketPriceChange`, `Bbs`.`Days7PriceChange`
FROM `BuyBack` `Bb`
join `Transaction` `Tr` on `Tr`.`ID` = `Bb`.`TransactionID`
join `BittrexAction` `Ba` on `Ba`.`TransactionID` = `Tr`.`ID` and `Ba`.`Type` in ('Sell','SpreadSell')
join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Tr`.`UserID`
join `User` `Us` on `Us`.`ID` = `Tr`.`UserID`
join `BearBullStats` `Bbs` on `Bbs`.`CoinID` =`Tr`.`CoinID`;

CREATE OR REPLACE VIEW `View10_DelayCoinPurchase` as
SELECT `Dcp`.`ID`, `Dcp`.`CoinID`, `Dcp`.`UserID`, `Dcp`.`DelayTime`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
, `Us`.`DisableUntil`
,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
, `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
, `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
, `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
, `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
, `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
, `Uc`.`TotalPurchasesPerCoin`
FROM `DelayCoinPurchase` `Dcp`
join `Coin` `Cn` on `Cn`.`ID` = `Dcp`.`CoinID`
join `User` `Us` on `Us`.`ID` = `Dcp`.`UserID`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Dcp`.`UserID`;

CREATE OR REPLACE VIEW `View11_CoinAlerts` as
SELECT `Ca`.`ID`, `Ca`.`CoinID`, `Ca`.`Action`, `Ca`.`Price`, `Ca`.`UserID`,`Ca`.`Status`, `Ca`.`Category`, `Ca`.`ReocurringAlert`, `Ca`.`DateTimeSent`, `Ca`.`CoinAlertRuleID`
,`Car`.`ID` as `IDCar`, `Car`.`Name`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name` as `NameCn`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinIDCp`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
, `Cmc`.`ID` as `IDCmc`, `Cmc`.`CoinID` as `CoinID2`, `Cmc`.`LiveMarketCap`, `Cmc`.`LastMarketCap`, ((`Cmc`.`LiveMarketCap`-`Cmc`.`LastMarketCap`)/`Cmc`.`LastMarketCap`)*100 as `MarketCapPctChange`
, `Cbo`.`ID` as `IDCbo`, `Cbo`.`CoinID` as `CoinID3`, `Cbo`.`LiveBuyOrders`, `Cbo`.`LastBuyOrders`, ((`Cbo`.`LiveBuyOrders`-`Cbo`.`LastBuyOrders`)/`Cbo`.`LastBuyOrders`)* 100 as `BuyOrdersPctChange`
, `Cv`.`ID` as `IDCv`, `Cv`.`CoinID` as `CoinID4`, `Cv`.`LiveVolume`, `Cv`.`LastVolume`, (( `Cv`.`LiveVolume`- `Cv`.`LastVolume`)/ `Cv`.`LastVolume`)*100 as `VolumePctChange`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`
, ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
,`Cso`.`ID`as `IDCso`, `Cso`.`CoinID` as `CoinIDCso`, `Cso`.`LiveSellOrders`, `Cso`.`LastSellOrders`,((`Cso`.`LiveSellOrders`-`Cso`.`LastSellOrders`)/`Cso`.`LastSellOrders`)*100 as `SellOrdersPctChange`
,if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` > 0, 1, if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` < 0, -1, 0)) as  `LivePriceTrend`
          ,if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` > 0, 1, if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` < 0, -1, 0)) as  `LastPriceTrend`
          ,if(`Cp`.`Price3` -`Cp`.`Price4` > 0, 1, if(`Cp`.`Price3` -`Cp`.`Price4` < 0, -1, 0)) as  `Price3Trend`
          ,if(`Cp`.`Price4` -`Cp`.`Price5` > 0, 1, if(`Cp`.`Price4` -`Cp`.`Price5` < 0, -1, 0)) as  `Price4Trend`
,if(`Cpc`.`Live1HrChange`-`Last1HrChange` >0,1,if(`Cpc`.`Live1HrChange`-`Last1HrChange` <0,-1,0)) as `1HrPriceChangeLive`
,if(`Last1HrChange`-`1HrChange3`>0,1,if(`Last1HrChange`-`1HrChange3`<0,-1,0)) as `1HrPriceChangeLast`
,if(`1HrChange3`-`1HrChange4`>0,1,if(`1HrChange3`-`1HrChange4`<0,-1,0)) as `1HrPriceChange3`
,if(`1HrChange4`-`1HrChange5`>0,1,if(`1HrChange4`-`1HrChange5`<0,-1,0)) as `1HrPriceChange4`
 ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
 , `Us`.`DisableUntil`
 ,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
 , `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
 , `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
 , `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
 , `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
 , `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
 , `Uc`.`TotalPurchasesPerCoin`
FROM `CoinAlerts` `Ca`
join `CoinAlertsRule`  `Car` on `Car`.`ID` = `Ca`.`CoinAlertRuleID`
join `Coin` `Cn` on `Cn`.`ID` = `Ca`.`CoinID`
join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Ca`.`CoinID`
join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Ca`.`CoinID`
join `CoinBuyOrders` `Cbo` on `Cbo`.`CoinID` = `Ca`.`CoinID`
join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Ca`.`CoinID`
join `CoinSellOrders` `Cso` on `Cso`.`CoinID` = `Ca`.`CoinID`
join `CoinVolume` `Cv` on `Cv`.`CoinID` = `Ca`.`CoinID`
join `User` `Us` on `Us`.`ID` = `Ca`.`UserID`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Ca`.`UserID`;

CREATE OR REPLACE VIEW `View12_UserConfig` as
Select `Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
, `Us`.`DisableUntil`
,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
, `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
, `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
, `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
, `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
, `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
, `Uc`.`TotalPurchasesPerCoin`
From `User` `Us`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Us`.`ID`;

CREATE OR REPLACE VIEW `View13_UserBuyRules` as
select `Br`.`ID` AS `RuleID`,`Br`.`RuleName` AS `RuleName`,`Br`.`UserID` AS `UserID`,`Br`.`BuyOrdersEnabled` AS `BuyOrdersEnabled`,`Br`.`BuyOrdersTop` AS `BuyOrdersTop`,`Br`.`BuyOrdersBtm` AS `BuyOrdersBtm`,`Br`.`MarketCapEnabled` AS `MarketCapEnabled`,`Br`.`MarketCapTop` AS `MarketCapTop`,`Br`.`MarketCapBtm` AS `MarketCapBtm`,`Br`.`1HrChangeEnabled` AS `1HrChangeEnabled`,`Br`.`1HrChangeTop` AS `1HrChangeTop`,`Br`.`1HrChangeBtm` AS `1HrChangeBtm`,`Br`.`24HrChangeEnabled` AS `24HrChangeEnabled`,`Br`.`24HrChangeTop` AS `24HrChangeTop`,`Br`.`24HrChangeBtm` AS `24HrChangeBtm`,`Br`.`7DChangeEnabled` AS `7DChangeEnabled`,`Br`.`7DChangeTop` AS `7DChangeTop`,`Br`.`7DChangeBtm` AS `7DChangeBtm`,`Br`.`CoinPriceEnabled` AS `CoinPriceEnabled`,`Br`.`CoinPriceTop` AS `CoinPriceTop`,`Br`.`CoinPriceBtm` AS `CoinPriceBtm`,`Br`.`SellOrdersEnabled` AS `SellOrdersEnabled`,`Br`.`SellOrdersTop` AS `SellOrdersTop`,`Br`.`SellOrdersBtm` AS `SellOrdersBtm`,`Br`.`VolumeEnabled` AS `VolumeEnabled`,`Br`.`VolumeTop` AS `VolumeTop`,`Br`.`VolumeBtm` AS `VolumeBtm`,`Br`.`BuyCoin` AS `BuyCoin`,`Br`.`SendEmail` AS `SendEmail`,`Br`.`BTCAmount` AS `BTCAmount`,`Br`.`BuyType` AS `BuyType`,`Br`.`CoinOrder` AS `CoinOrder`,`Br`.`BuyCoinOffsetEnabled` AS `BuyCoinOffsetEnabled`,`Br`.`BuyCoinOffsetPct` AS `BuyCoinOffsetPct`,`Br`.`PriceTrendEnabled` AS `PriceTrendEnabled`,`Br`.`Price4Trend` AS `Price4Trend`,`Br`.`Price3Trend` AS `Price3Trend`,`Br`.`LastPriceTrend` AS `LastPriceTrend`,`Br`.`LivePriceTrend` AS `LivePriceTrend`,`Br`.`BuyPriceMinEnabled` AS `BuyPriceMinEnabled`,`Br`.`BuyPriceMin` AS `BuyPriceMin`,`Br`.`LimitToCoin` AS `LimitToCoin`,`Br`.`LimitToCoinID` AS `LimitToCoinID`,`Br`.`AutoBuyCoinEnabled` AS `AutoBuyCoinEnabled`,`Br`.`AutoBuyCoinPct` AS `AutoBuyCoinPct`,`Br`.`BuyAmountOverrideEnabled` AS `BuyAmountOverrideEnabled`,`Br`.`BuyAmountOverride` AS `BuyAmountOverride`,`Br`.`NewBuyPattern` AS `NewBuyPattern`,`Br`.`SellRuleFixed` AS `SellRuleFixed`,`Br`.`OverrideDailyLimit` AS `OverrideDailyLimit`,`Br`.`CoinPricePatternEnabled` AS `CoinPricePatternEnabled`,`Br`.`CoinPricePattern` AS `CoinPricePattern`,`Br`.`1HrChangeTrendEnabled` AS `1HrChangeTrendEnabled`,`Br`.`1HrChangeTrend` AS `1HrChangeTrend`,`Br`.`CoinPriceMatchPattern` AS `CoinPriceMatchPattern`,`Br`.`CoinPriceMatchID` AS `CoinPriceMatchID`,`Br`.`CoinPricePatternID` AS `CoinPricePatternID`,`Br`.`Coin1HrPatternID` AS `Coin1HrPatternID`,`Br`.`BuyRisesInPrice` AS `BuyRisesInPrice`,`Br`.`DisableUntil` AS `DisableUntil`,`Br`.`OverrideDisableRule` AS `OverrideDisableRule`,`Br`.`LimitBuyAmountEnabled` AS `LimitBuyAmountEnabled`,`Br`.`LimitBuyAmount` AS `LimitBuyAmount`,`Br`.`OverrideCancelBuyTimeEnabled` AS `OverrideCancelBuyTimeEnabled`,`Br`.`OverrideCancelBuyTimeMins` AS `OverrideCancelBuyTimeMins`,`Br`.`LimitBuyTransactionsEnabled` AS `LimitBuyTransactionsEnabled`,`Br`.`LimitBuyTransactions` AS `LimitBuyTransactions`,`Br`.`NoOfBuyModeOverrides` AS `NoOfBuyModeOverrides`,`Br`.`CoinModeOverridePriceEnabled` AS `CoinModeOverridePriceEnabled`,`Br`.`BuyModeActivate` AS `BuyModeActivate`,`Br`.`CoinMode` AS `CoinMode`,`Br`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Br`.`OneTimeBuyRule` AS `OneTimeBuyRule`,`Br`.`LimitToBaseCurrency` AS `LimitToBaseCurrency`,`Br`.`EnableRuleActivationAfterDip` AS `EnableRuleActivationAfterDip`,`Br`.`24HrPriceDipPct` AS `24HrPriceDipPct`,`Br`.`7DPriceDipPct` AS `7DPriceDipPct`,`Br`.`BuyAmountCalculationEnabled` AS `BuyAmountCalculationEnabled`,`Br`.`TotalPurchasesPerRule` AS `TotalPurchasesPerRule`,
`Us`.`ID` AS `IDUs`,`Us`.`AccountType` AS `AccountType`,`Us`.`Active` AS `Active`,`Us`.`UserName` AS `UserName`,`Us`.`Password` AS `Password`,`Us`.`ExpiryDate` AS `ExpiryDate`,`Us`.`FirstTimeLogin` AS `FirstTimeLogin`,`Us`.`ResetComplete` AS `ResetComplete`,`Us`.`ResetToken` AS `ResetToken`,`Us`.`Email` AS `Email`,`Us`.`DisableUntil` AS `DisableUntilUs`
,`Uc`.`UserID` AS `UserID2`,`Uc`.`APIKey` AS `APIKey`,`Uc`.`APISecret` AS `APISecret`,`Uc`.`EnableDailyBTCLimit` AS `EnableDailyBTCLimit`,`Uc`.`EnableTotalBTCLimit` AS `EnableTotalBTCLimit`,`Uc`.`DailyBTCLimit` AS `DailyBTCLimit`,`Uc`.`TotalBTCLimit` AS `TotalBTCLimit`,`Uc`.`BTCBuyAmount` AS `BTCBuyAmount`,`Uc`.`CoinSellOffsetEnabled` AS `CoinSellOffsetEnabled2`,`Uc`.`CoinSellOffsetPct` AS `CoinSellOffsetPct2`,`Uc`.`BaseCurrency` AS `BaseCurrency2`,`Uc`.`NoOfCoinPurchase` AS `NoOfCoinPurchase`,`Uc`.`TimetoCancelBuy` AS `TimetoCancelBuy`,`Uc`.`TimeToCancelBuyMins` AS `TimeToCancelBuyMins`,`Uc`.`KEK` AS `KEK`,`Uc`.`MinsToPauseAlert` AS `MinsToPauseAlert`,`Uc`.`LowPricePurchaseEnabled` AS `LowPricePurchaseEnabled`,`Uc`.`NoOfPurchases` AS `NoOfPurchases2`,`Uc`.`PctToPurchase` AS `PctToPurchase`,`Uc`.`TotalRisesInPrice` AS `TotalRisesInPrice`,`Uc`.`TotalRisesInPriceSell` AS `TotalRisesInPriceSell`,`Uc`.`ReservedUSDT` AS `ReservedUSDT`,`Uc`.`ReservedBTC` AS `ReservedBTC`,`Uc`.`ReservedETH` AS `ReservedETH`,`Uc`.`TotalProfitPauseEnabled` AS `TotalProfitPauseEnabled`,`Uc`.`TotalProfitPause` AS `TotalProfitPause`,`Uc`.`PauseRulesEnabled` AS `PauseRulesEnabled`,`Uc`.`PauseRules` AS `PauseRules`,`Uc`.`PauseHours` AS `PauseHours`,`Uc`.`MergeAllCoinsDaily` AS `MergeAllCoinsDaily`,`Uc`.`MarketDropStopEnabled` AS `MarketDropStopEnabled`,`Uc`.`MarketDropStopPct` AS `MarketDropStopPct`,`Uc`.`SellAllCoinsEnabled` AS `SellAllCoinsEnabled`,`Uc`.`SellAllCoinsPct` AS `SellAllCoinsPct`,`Uc`.`CoinModeEmails` AS `CoinModeEmails`,`Uc`.`CoinModeEmailsSell` AS `CoinModeEmailsSell`,`Uc`.`CoinModeMinsToCancelBuy` AS `CoinModeMinsToCancelBuy`,`Uc`.`PctToSave` AS `PctToSave`,`Uc`.`SplitBuyAmounByPctEnabled` AS `SplitBuyAmounByPctEnabled`,`Uc`.`NoOfSplits` AS `NoOfSplits`,`Uc`.`SaveResidualCoins` AS `SaveResidualCoins`,`Uc`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Uc`.`SpreadBetRuleID` AS `SpreadBetRuleIDUc`,`Uc`.`MinsToPauseAfterPurchase` AS `MinsToPauseAfterPurchase`,`Uc`.`LowMarketModeEnabled` AS `LowMarketModeEnabled`,`Uc`.`LowMarketModeDate` AS `LowMarketModeDate`,`Uc`.`AutoMergeSavings` AS `AutoMergeSavings`,`Uc`.`AllBuyBackAsOverride` AS `AllBuyBackAsOverride`,`Uc`.`TotalPurchasesPerCoin` AS `TotalPurchasesPerCoin`
,`Cn`.`ID` AS `IDCn`,`Cn`.`Symbol` AS `Symbol`,`Cn`.`Name` AS `NameCn`,`Cn`.`BaseCurrency` AS `BaseCurrency`,`Cn`.`BuyCoin` AS `BuyCoin2`,`Cn`.`CMCID` AS `CMCID`,`Cn`.`SecondstoUpdate` AS `SecondstoUpdate`,`Cn`.`Image` AS `Image`,`Cn`.`MinTradeSize` AS `MinTradeSize`,`Cn`.`CoinPrecision` AS `CoinPrecision`,`Cn`.`DoNotBuy` AS `DoNotBuy`
,'AutoBuyPrice'
,`Cpmn`.`ID` as `IDCpmn`, `Cpmn`.`Name` as `NameCpmn`, `Cpmn`.`UserID` as `UserIDCpmn`, `Cpmn`.`BuySell`, `Cpmn`.`PriceProjectionEnabled`
,`Cppn`.`ID` as `IDCppn`, `Cppn`.`Name` as `NameCppn`, `Cppn`.`UserID` as `UserIDCppn`, `Cppn`.`BuySell` as `BuySellCppn`
,`C1hPn`.`ID` as `IDC1hPn`, `C1hPn`.`Name` as `NameC1hPn`, `C1hPn`.`UserID` as `UserIDC1hPn`, `C1hPn`.`BuySell` as `BuySellC1hPn`
from (((`BuyRules` `Br`
  join `User` `Us` on((`Us`.`ID` = `Br`.`UserID`)))
  join `UserConfig` `Uc` on((`Uc`.`UserID` = `Us`.`ID`)))
  left join `Coin` `Cn` on((`Cn`.`ID` = `Br`.`LimitToCoinID`)))
  left join `CoinPriceMatchName` `Cpmn` on `Cpmn`.`ID` = `Br`.`CoinPriceMatchID`
  left join `CoinPricePatternName` `Cppn` on `Cppn`.`ID` = `Br`.`CoinPricePatternID`
  left join  `Coin1HrPatternName` `C1hPn` on `C1hPn`.`ID` = `Br`.`Coin1HrPatternID`;


  CREATE OR REPLACE VIEW `View14_UserSellRules` as
  SELECT `Sr`.`ID`, `Sr`.`RuleName`, `Sr`.`UserID`, `Sr`.`SellCoin`, `Sr`.`SendEmail`, `Sr`.`BuyOrdersEnabled`, `Sr`.`BuyOrdersTop`, `Sr`.`BuyOrdersBtm`, `Sr`.`MarketCapEnabled`, `Sr`.`MarketCapTop`, `Sr`.`MarketCapBtm`, `Sr`.`1HrChangeEnabled`, `Sr`.`1HrChangeTop`, `Sr`.`1HrChangeBtm`, `Sr`.`24HrChangeEnabled`, `Sr`.`24HrChangeTop`, `Sr`.`24HrChangeBtm`, `Sr`.`7DChangeEnabled`, `Sr`.`7DChangeTop`, `Sr`.`7DChangeBtm`, `Sr`.`ProfitPctEnabled`, `Sr`.`ProfitPctTop`, `Sr`.`ProfitPctBtm`, `Sr`.`CoinPriceEnabled`, `Sr`.`CoinPriceTop`, `Sr`.`CoinPriceBtm`, `Sr`.`SellOrdersEnabled`, `Sr`.`SellOrdersTop`, `Sr`.`SellOrdersBtm`, `Sr`.`VolumeEnabled`, `Sr`.`VolumeTop`, `Sr`.`VolumeBtm`, `Sr`.`CoinOrder`, `Sr`.`SellCoinOffsetEnabled`, `Sr`.`SellCoinOffsetPct`, `Sr`.`SellPriceMinEnabled`, `Sr`.`SellPriceMin`, `Sr`.`LimitToCoin`, `Sr`.`LimitToCoinID`, `Sr`.`AutoSellCoinEnabled`, `Sr`.`AutoSellCoinPct`, `Sr`.`SellPatternEnabled`, `Sr`.`SellPattern`, `Sr`.`LimitToBuyRule`, `Sr`.`CoinPricePatternEnabled`, `Sr`.`CoinPricePattern`, `Sr`.`CoinPriceMatchNameID`, `Sr`.`CoinPricePatternNameID`, `Sr`.`CoinPrice1HrPatternNameID`, `Sr`.`SellFallsInPrice`, `Sr`.`CoinModeRule`, `Sr`.`CoinSwapEnabled`, `Sr`.`CoinSwapAmount`, `Sr`.`NoOfCoinSwapsPerWeek`, `Sr`.`MergeCoinEnabled`
 ,`Us`.`ID` AS `IDUs`,`Us`.`AccountType` AS `AccountType`,`Us`.`Active` AS `Active`,`Us`.`UserName` AS `UserName`,`Us`.`Password` AS `Password`,`Us`.`ExpiryDate` AS `ExpiryDate`,`Us`.`FirstTimeLogin` AS `FirstTimeLogin`,`Us`.`ResetComplete` AS `ResetComplete`,`Us`.`ResetToken` AS `ResetToken`,`Us`.`Email` AS `Email`,`Us`.`DisableUntil` AS `DisableUntilUs`
  ,`Uc`.`UserID` AS `UserID2`,`Uc`.`APIKey` AS `APIKey`,`Uc`.`APISecret` AS `APISecret`,`Uc`.`EnableDailyBTCLimit` AS `EnableDailyBTCLimit`,`Uc`.`EnableTotalBTCLimit` AS `EnableTotalBTCLimit`,`Uc`.`DailyBTCLimit` AS `DailyBTCLimit`,`Uc`.`TotalBTCLimit` AS `TotalBTCLimit`,`Uc`.`BTCBuyAmount` AS `BTCBuyAmount`,`Uc`.`CoinSellOffsetEnabled` AS `CoinSellOffsetEnabled2`,`Uc`.`CoinSellOffsetPct` AS `CoinSellOffsetPct2`,`Uc`.`BaseCurrency` AS `BaseCurrency2`,`Uc`.`NoOfCoinPurchase` AS `NoOfCoinPurchase`,`Uc`.`TimetoCancelBuy` AS `TimetoCancelBuy`,`Uc`.`TimeToCancelBuyMins` AS `TimeToCancelBuyMins`,`Uc`.`KEK` AS `KEK`,`Uc`.`MinsToPauseAlert` AS `MinsToPauseAlert`,`Uc`.`LowPricePurchaseEnabled` AS `LowPricePurchaseEnabled`,`Uc`.`NoOfPurchases` AS `NoOfPurchases2`,`Uc`.`PctToPurchase` AS `PctToPurchase`,`Uc`.`TotalRisesInPrice` AS `TotalRisesInPrice`,`Uc`.`TotalRisesInPriceSell` AS `TotalRisesInPriceSell`,`Uc`.`ReservedUSDT` AS `ReservedUSDT`,`Uc`.`ReservedBTC` AS `ReservedBTC`,`Uc`.`ReservedETH` AS `ReservedETH`,`Uc`.`TotalProfitPauseEnabled` AS `TotalProfitPauseEnabled`,`Uc`.`TotalProfitPause` AS `TotalProfitPause`,`Uc`.`PauseRulesEnabled` AS `PauseRulesEnabled`,`Uc`.`PauseRules` AS `PauseRules`,`Uc`.`PauseHours` AS `PauseHours`,`Uc`.`MergeAllCoinsDaily` AS `MergeAllCoinsDaily`,`Uc`.`MarketDropStopEnabled` AS `MarketDropStopEnabled`,`Uc`.`MarketDropStopPct` AS `MarketDropStopPct`,`Uc`.`SellAllCoinsEnabled` AS `SellAllCoinsEnabled`,`Uc`.`SellAllCoinsPct` AS `SellAllCoinsPct`,`Uc`.`CoinModeEmails` AS `CoinModeEmails`,`Uc`.`CoinModeEmailsSell` AS `CoinModeEmailsSell`,`Uc`.`CoinModeMinsToCancelBuy` AS `CoinModeMinsToCancelBuy`,`Uc`.`PctToSave` AS `PctToSave`,`Uc`.`SplitBuyAmounByPctEnabled` AS `SplitBuyAmounByPctEnabled`,`Uc`.`NoOfSplits` AS `NoOfSplits`,`Uc`.`SaveResidualCoins` AS `SaveResidualCoins`,`Uc`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Uc`.`SpreadBetRuleID` AS `SpreadBetRuleIDUc`,`Uc`.`MinsToPauseAfterPurchase` AS `MinsToPauseAfterPurchase`,`Uc`.`LowMarketModeEnabled` AS `LowMarketModeEnabled`,`Uc`.`LowMarketModeDate` AS `LowMarketModeDate`,`Uc`.`AutoMergeSavings` AS `AutoMergeSavings`,`Uc`.`AllBuyBackAsOverride` AS `AllBuyBackAsOverride`,`Uc`.`TotalPurchasesPerCoin` AS `TotalPurchasesPerCoin`
  FROM `SellRules` `Sr`
  join `User` `Us` on `Us`.`ID` = `Sr`.`UserID`
  join `UserConfig` `Uc` on `Uc`.`UserID` = `Us`.`ID`;
