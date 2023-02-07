

CREATE OR REPLACE VIEW `View1_BuyCoins` as
SELECT `Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
, `Cmc`.`ID` as `IDCmc`, `Cmc`.`CoinID` as `CoinID2`, `Cmc`.`LiveMarketCap`, `Cmc`.`LastMarketCap`, ((`Cmc`.`LiveMarketCap`-`Cmc`.`LastMarketCap`)/`Cmc`.`LastMarketCap`)*100 as `MarketCapPctChange`
, `Cbo`.`ID` as `IDCbo`, `Cbo`.`CoinID` as `CoinID3`, `Cbo`.`LiveBuyOrders`, `Cbo`.`LastBuyOrders`, ((`Cbo`.`LiveBuyOrders`-`Cbo`.`LastBuyOrders`)/`Cbo`.`LastBuyOrders`)* 100 as `BuyOrdersPctChange`
, `Cv`.`ID` as `IDCv`, `Cv`.`CoinID` as `CoinID4`, `Cv`.`LiveVolume`, `Cv`.`LastVolume`, (( `Cv`.`LiveVolume`- `Cv`.`LastVolume`)/ `Cv`.`LastVolume`)*100 as `VolumePctChange`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cp`.`LiveCoinPrice`-`Cpc`.`Live1HrChange`)/`Cpc`.`Live1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cp`.`LiveCoinPrice`- `Cpc`.`Live24HrChange`)/ `Cpc`.`Live24HrChange`)*100 as `Hr24ChangePctChange`
, ((`Cp`.`LiveCoinPrice`-`Cpc`.`Live7DChange`)/`Cpc`.`Live7DChange`)*100 as `D7ChangePctChange`
,`Cso`.`ID`as `IDCso`, `Cso`.`CoinID` as `CoinIDCso`, `Cso`.`LiveSellOrders`, `Cso`.`LastSellOrders`,((`Cso`.`LiveSellOrders`-`Cso`.`LastSellOrders`)/`Cso`.`LastSellOrders`)*100 as `SellOrdersPctChange`
,if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` > 0, 1, if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` < 0, -1, 0)) as  `LivePriceTrend`
          ,if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` > 0, 1, if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` < 0, -1, 0)) as  `LastPriceTrend`
          ,if(`Cp`.`Price3` -`Cp`.`Price4` > 0, 1, if(`Cp`.`Price3` -`Cp`.`Price4` < 0, -1, 0)) as  `Price3Trend`
          ,if(`Cp`.`Price4` -`Cp`.`Price5` > 0, 1, if(`Cp`.`Price4` -`Cp`.`Price5` < 0, -1, 0)) as  `Price4Trend`
,if(`Cpc`.`Live1HrChange`-`Last1HrChange` >0,1,if(`Cpc`.`Live1HrChange`-`Last1HrChange` <0,-1,0)) as `1HrPriceChangeLive`
,if(`Last1HrChange`-`1HrChange3`>0,1,if(`Last1HrChange`-`1HrChange3`<0,-1,0)) as `1HrPriceChangeLast`
,if(`1HrChange3`-`1HrChange4`>0,1,if(`1HrChange3`-`1HrChange4`<0,-1,0)) as `1HrPriceChange3`
,if(`1HrChange4`-`1HrChange5`>0,1,if(`1HrChange4`-`1HrChange5`<0,-1,0)) as `1HrPriceChange4`
,`Pdcs`.`ID` as `IDPdcs`, `Pdcs`.`CoinID` as `CoinIDPdcs`, `Pdcs`.`PriceDipEnabled` as `PriceDipEnabledPdcs`, `Pdcs`.`HoursFlat` as `HoursFlatPdcs`, `Pdcs`.`DipStartTime` as `DipStartTimePdcs`, `Pdcs`.`HoursFlatLow` as `HoursFlatLowPdcs`, `Pdcs`.`HoursFlatHigh` as `HoursFlatHighPdcs`,`Pdcs`.`MaxHoursFlat`
,avgMinPrice(`Cn`.`ID`,20) as `MinPriceFromLow`, ((`Cp`.`LiveCoinPrice`- avgMinPrice(`Cn`.`ID`,20))/avgMinPrice(`Cn`.`ID`,20))*100 as `PctFromLiveToLow`
,'ID' as `IDAhl`, 'HighLow', `v19Athl`.`Month3High`, `v19Athl`.`Month6High`, `v19Athl`.`CoinID` as `CoinIDv19Athl`, 'LastUpdated' as `LastUpdatedAhl` ,(`v19Athl`.`Month6Low`+`v19Athl`.`Month3Low`)/2 as AverageLowPrice, TIMESTAMPDIFF(HOUR, `v19Athl`.`DateAdded`, now()) as HoursSinceAdded, `v19Athl`.`Month3Low`, `v19Athl`.`Month6Low`
,`Caa`.`ID` as `CaaID`, `Caa`.`CoinID` as `CaaCoinID`, `Caa`.`Offset` as `CaaOffset`, `Caa`.`MinsToCancelBuy` as `CaaMinsToCancelBuy`, `Caa`.`MinsToCancelSell`as `CaaMinsToCancelSell`
FROM `Coin` `Cn`
join `CoinBidPrice` `Cp` on `Cp`.`CoinID` = `Cn`.`ID`
join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Cn`.`ID`
join `CoinBuyOrders` `Cbo` on `Cbo`.`CoinID` = `Cn`.`ID`
join `CoinVolume` `Cv` on `Cv`.`CoinID` = `Cn`.`ID`
join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cn`.`ID`
join `CoinSellOrders` `Cso` on `Cso`.`CoinID` = `Cn`.`ID`
Left join `PriceDipCoinStatus` `Pdcs` on `Pdcs`.`CoinID` =  `Cn`.`ID`
left Join `View19_MaxHighLow` `v19Athl` on `v19Athl`.`CoinID` = `Cn`.`ID`
left Join `CoinAutoActions` `Caa` on `Caa`.`CoinID` = `Cn`.`ID`
  where `Cn`.`BuyCoin` = 1;

CREATE OR REPLACE VIEW `View2_TrackingBuyCoins` as
SELECT `Tc`.`ID` as `IDTc`, `Tc`.`CoinID`, `Tc`.`CoinPrice`, `Tc`.`TrackDate`, `Tc`.`UserID`, `Tc`.`BaseCurrency` as `TrackingBaseCurrency`, `Tc`.`SendEmail`, `Tc`.`BuyCoin`, `Tc`.`Quantity`
, `Tc`.`RuleIDBuy`, `Tc`.`CoinSellOffsetPct`, `Tc`.`CoinSellOffsetEnabled`, `Tc`.`BuyType`, `Tc`.`MinsToCancelBuy`, `Tc`.`SellRuleFixed`, `Tc`.`Status` as `TrackingStatus`, `Tc`.`ToMerge`
, `Tc`.`NoOfPurchases`, `Tc`.`NoOfRisesInPrice`, `Tc`.`OriginalPrice`, `Tc`.`BuyRisesInPrice`, `Tc`.`TransactionID`, `Tc`.`Type` as `TrackingType`, `Tc`.`LastPrice`, `Tc`.`SBRuleID`
, `Tc`.`SBTransID`, `Tc`.`quickBuyCount`, `Tc`.`OverrideCoinAllocation`, `Tc`.`CoinSwapID`, `Tc`.`OldBuyBackTransID`, `Tc`.`BaseBuyPrice`,`Tc`.`ReduceLossCounter`,`Tc`.`SavingOverride`
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
, `Uc`.`TotalPurchasesPerCoin`,`Uc`.`PctOfAuto`
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
,`Athl`.`ID` as `IDAthl`, `Athl`.`CoinID` as `CoinID3`, `Athl`.`HighLow`
, `v19Athl`.`HighPrice` as `ATHPrice`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`
, ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
,`Pdcs`.`ID` as `IDPdcs`, `Pdcs`.`CoinID` as `CoinIDPdcs`, `Pdcs`.`PriceDipEnabled` as `PriceDipEnabledPdcs`, `Pdcs`.`HoursFlat` as `HoursFlatPdcs`, `Pdcs`.`DipStartTime` as `DipStartTimePdcs`, `Pdcs`.`HoursFlatLow` as `HoursFlatLowPdcs`, `Pdcs`.`HoursFlatHigh` as `HoursFlatHighPdcs`,`Pdcs`.`MaxHoursFlat`
FROM `TrackingCoins` `Tc`
join `CoinBidPrice` `Cp` on `Cp`.`CoinID` =   `Tc`.`CoinID`
join `Coin` `Cn` on `Cn`.`ID` = `Cp`.`CoinID`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Tc`.`UserID`
join `User` `Us` on `Us`.`ID` = `Tc`.`UserID`
join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cn`.`ID`
Left join `BuyRules` `Br` on `Br`.`ID` = `Tc`.`RuleIDBuy`
left join `AllTimeHighLow` `Athl` on `Athl`.`CoinID` = `Tc`.`CoinID` and `Athl`.`HighLow` = 'High'
left Join `View19_MaxHighLow` `v19Athl` on `v19Athl`.`CoinID` = `Tc`.`CoinID`
Left join `PriceDipCoinStatus` `Pdcs` on `Pdcs`.`CoinID` =  `Cn`.`ID`;


CREATE OR REPLACE VIEW `View3_SpreadBetBuy` as
SELECT `Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, sum(`Cp`.`LiveCoinPrice`) as LiveCoinPrice, sum(`Cp`.`LastCoinPrice`) as LastCoinPrice, sum(`Cp`.`Price3`) as Price3, sum(`Cp`.`Price4`) as Price4, sum(`Cp`.`Price5`) as Price5, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, sum(`Cpc`.`Live1HrChange`) as Live1HrChange, sum(`Cpc`.`Last1HrChange`) as Last1HrChange, sum(`Cpc`.`Live24HrChange`) as Live24HrChange, sum(`Cpc`.`Last24HrChange`) as Last24HrChange, sum(`Cpc`.`Live7DChange`) as Live7DChange, sum(`Cpc`.`Last7DChange`) as Last7DChange, sum(`Cpc`.`1HrChange3`) as 1HrChange3
, sum(`Cpc`.`1HrChange4`) as 1HrChange4, sum(`Cpc`.`1HrChange5`) as 1HrChange5, ((sum(`Cpc`.`Live1HrChange`)-sum(`Cpc`.`Last1HrChange`))/sum(`Cpc`.`Last1HrChange`))*100  as `Hr1ChangePctChange`, (( sum(`Cpc`.`Last24HrChange`)- sum(`Cpc`.`Last24HrChange`))/ sum(`Cpc`.`Last24HrChange`))*100 as `Hr24ChangePctChange`
, ((sum(`Cpc`.`Live7DChange`)-sum(`Cpc`.`Last7DChange`))/sum(`Cpc`.`Last7DChange`))*100 as `D7ChangePctChange`
 ,`Sbc`.`SpreadBetRuleID` as SpreadBetRuleIDSbc, `Sbc`.`CoinID` as CoinIDSbc
 ,`Sbr`.`Name` as NameSbr, `Sbr`.`UserID` as UserIDSbr
 ,`Sbt`.`ID` as IDsbt,`Sbt`.`SpreadBetRuleID` as SpreadBetRuleIDSbt, `Sbt`.`TotalAmountToBuy`, `Sbt`.`AmountPerCoin`
 ,`Sbs`.`SpreadBetRuleID`as SpreadBetRuleIDSbs, `Sbs`.`Hr1BuyPrice`, `Sbs`.`Hr24BuyPrice`, `Sbs`.`D7BuyPrice`, `Sbs`.`NextReviewDate`, `Sbs`.`PctProfitSell`, `Sbs`.`NoOfTransactions`, `Sbs`.`LowestPctProfit`, `Sbs`.`AvgTimeToSell`, `Sbs`.`Enabled`, `Sbs`.`Hr1BuyEnable`, `Sbs`.`Hr1BuyDisable`, `Sbs`.`Hr24andD7StartPrice`, `Sbs`.`Month6TotalPrice`, `Sbs`.`AllTimeTotalPrice`, `Sbs`.`Hr1EnableStartPrice`, `Sbs`.`BuyFallsinPrice`, `Sbs`.`SellRaisesInPrice`, `Sbs`.`MinsToCancel`, `Sbs`.`CalculatedMinsToCancel`, `Sbs`.`CalculatedRisesInPrice`, `Sbs`.`CalculatedFallsinPrice`, `Sbs`.`AutoBuyBackSell`
 ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
 , `Us`.`DisableUntil`
 ,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
 , `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
 , `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
 , `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
 , `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
 , `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
 , `Uc`.`TotalPurchasesPerCoin`,`Uc`.`PctOfAuto`
 ,`Ath`.`ID` as `IDAth`, `Ath`.`CoinID` as `CoinID3`, `Ath`.`HighLow`, `Ath`.`Price` as PriceAth
         FROM `Coin` `Cn`
join `CoinBidPrice` `Cp` on `Cp`.`CoinID` = `Cn`.`ID`
join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cn`.`ID`
join `SpreadBetCoins` `Sbc` on `Sbc`.`CoinID` =  `Cn`.`ID`
join `SpreadBetRules` `Sbr` on `Sbr`.`ID` = `Sbc`.`SpreadBetRuleID`
join `SpreadBetTransactions` `Sbt` on `Sbt`.`SpreadBetRuleID` = `Sbr`.`ID`
join `SpreadBetSettings` `Sbs` on `Sbs`.`SpreadBetRuleID` = `Sbr`.`ID`
join `User` `Us` on `Us`.`ID` = `Sbr`.`UserID`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Us`.`ID`
Join `AllTimeHighLow` `Ath` on `Ath`.`CoinID` =  `Cn`.`ID` and `HighLow` = 'High'
group by `Sbc`.`SpreadBetRuleID`;

CREATE OR REPLACE VIEW `View4_BittrexBuySell` as
SELECT `Ba`.`ID` as `IDBa`, `Ba`.`CoinID`as `CoinID4`, `Ba`.`TransactionID`, `Ba`.`UserID` AS `UserIDBa`, `Ba`.`Type` as `TypeBa`, `Ba`.`BittrexRef` as `BittrexRefBa`, `Ba`.`ActionDate`, `Ba`.`CompletionDate` as `CompletionDateBa`, `Ba`.`Status` as `StatusBa`, `Ba`.`SellPrice`, `Ba`.`RuleID`, `Ba`.`RuleIDSell`, `Ba`.`QuantityFilled`, `Ba`.`MultiplierPrice`, `Ba`.`BuyBack`, `Ba`.`OldBuyBackTransID`, `Ba`.`ResidualAmount`,`Ba`.`MultiSellRuleID`,`Ba`.`ReduceLossBuy`,`Ba`.`MinsToCancelAction`,`Ba`.`TimeToCancel` as TimeToCancelBa
,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
, `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
, `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
, `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
, `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
, `Uc`.`RedirectPurchasesToSpread` as `RedirectPurchasesToSpreadUc`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
, `Uc`.`TotalPurchasesPerCoin`,`Uc`.`MergeSavingWithPurchase`,`Uc`.`BuyBackEnabled`,`Uc`.`HoursFlatTolerance`,`Uc`.`RedirectPurchasesToSpreadID`,`Uc`.`SellSavingsEnabled`,`Uc`.`RebuySavingsEnabled`,`Uc`.`LowMarketModeStartPct`,`Uc`.`LowMarketModeIncrements`,`Uc`.`SaveMode`,`Uc`.`BuyBackMax`
, `Uc`.`PauseCoinIDAfterPurchaseEnabled`, `Uc`.`DaysToPauseCoinIDAfterPurchase`,`Uc`.`BuyBackHoursFlatTarget`,`Uc`.`HoldCoinForBuyOut`,`Uc`.`CoinForBuyOutPct`,`Uc`.`PctToCancelBittrexAction`,`Uc`.`SavingPctOfTotalEnabled`,`Uc`.`SavingPctOfTotal`,`Uc`.`PctOfAuto`,`Uc`.`BuyBackHoursFlatAutoEnabled`,`Uc`.`PctOfAutoBuyBack`,`Uc`.`PctOfAutoReduceLoss`,`Uc`.`BuyBackMinsToCancel`,`Uc`.`BuyBackAutoPct`
,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
, `Us`.`DisableUntil`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
,`Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID3`,`Tr`.`UserID` AS `UserID3`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `Status`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRef`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`,`Tr`.`StopBuyBack`,`Tr`.`holdingAmount`,`Tr`.`OverrideBittrexCancellation`,`Tr`.`OverrideBBAmount`,`Tr`.`OverrideBBSaving`,`Tr`.`BuyBackCounter`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`
, ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
,`Sr`.`ID` as `IDSr`, `Sr`.`RuleName`, `Sr`.`UserID` as `sUserID`, `Sr`.`SellCoin`, `Sr`.`SendEmail`, `Sr`.`BuyOrdersEnabled`, `Sr`.`BuyOrdersTop`, `Sr`.`BuyOrdersBtm`, `Sr`.`MarketCapEnabled`, `Sr`.`MarketCapTop`, `Sr`.`MarketCapBtm`, `Sr`.`1HrChangeEnabled`, `Sr`.`1HrChangeTop`, `Sr`.`1HrChangeBtm`, `Sr`.`24HrChangeEnabled`, `Sr`.`24HrChangeTop`, `Sr`.`24HrChangeBtm`, `Sr`.`7DChangeEnabled`, `Sr`.`7DChangeTop`, `Sr`.`7DChangeBtm`, `Sr`.`ProfitPctEnabled`, `Sr`.`ProfitPctTop`, `Sr`.`ProfitPctBtm`, `Sr`.`CoinPriceEnabled`, `Sr`.`CoinPriceTop`, `Sr`.`CoinPriceBtm`, `Sr`.`SellOrdersEnabled`, `Sr`.`SellOrdersTop`, `Sr`.`SellOrdersBtm`, `Sr`.`VolumeEnabled`, `Sr`.`VolumeTop`, `Sr`.`VolumeBtm`, `Sr`.`CoinOrder`, `Sr`.`SellCoinOffsetEnabled`, `Sr`.`SellCoinOffsetPct`, `Sr`.`SellPriceMinEnabled`, `Sr`.`SellPriceMin`, `Sr`.`LimitToCoin`, `Sr`.`LimitToCoinID`, `Sr`.`AutoSellCoinEnabled`, `Sr`.`AutoSellCoinPct`, `Sr`.`SellPatternEnabled`, `Sr`.`SellPattern`, `Sr`.`LimitToBuyRule`, `Sr`.`CoinPricePatternEnabled`, `Sr`.`CoinPricePattern`, `Sr`.`CoinPriceMatchNameID`, `Sr`.`CoinPricePatternNameID`, `Sr`.`CoinPrice1HrPatternNameID`, `Sr`.`SellFallsInPrice`, `Sr`.`CoinModeRule`, `Sr`.`CoinSwapEnabled`, `Sr`.`CoinSwapAmount`, `Sr`.`NoOfCoinSwapsPerWeek`, `Sr`.`MergeCoinEnabled`,`Sr`.`OverrideBuyBackAmount`, `Sr`.`OverrideBuyBackSaving`
,TIMESTAMPDIFF(DAY,CURDATE(),`Ba`.`ActionDate`) as `DaysOutstanding`
,DateDiff(`Ba`.`ActionDate`,now()) as `timeSinceAction`
,TIMESTAMPDIFF(MINUTE,`Ba`.`ActionDate`,NOW()) as `MinsSinceAction`
,DateDiff(now(),`Tr`.`BuyOrderCancelTime`) as `BuyOrderCancelTimeMins`
,`Br`.`ID` AS `RuleIDBr`,`Br`.`RuleName` AS `RuleNameBr`,`Br`.`UserID` AS `UserIDBr`,`Br`.`BuyOrdersEnabled` AS `BuyOrdersEnabledBr`,`Br`.`BuyOrdersTop` AS `BuyOrdersTopBr`,`Br`.`BuyOrdersBtm` AS `BuyOrdersBtmBr`,`Br`.`MarketCapEnabled` AS `MarketCapEnabledBr`,`Br`.`MarketCapTop` AS `MarketCapTopBr`,`Br`.`MarketCapBtm` AS `MarketCapBtmBr`,`Br`.`1HrChangeEnabled` AS `1HrChangeEnabledBr`,`Br`.`1HrChangeTop` AS `1HrChangeTopBr`,`Br`.`1HrChangeBtm` AS `1HrChangeBtmBr`,`Br`.`24HrChangeEnabled` AS `24HrChangeEnabledBr`,`Br`.`24HrChangeTop` AS `24HrChangeTopBr`,`Br`.`24HrChangeBtm` AS `24HrChangeBtmBr`,`Br`.`7DChangeEnabled` AS `7DChangeEnabledBr`,`Br`.`7DChangeTop` AS `7DChangeTopBr`,`Br`.`7DChangeBtm` AS `7DChangeBtmBr`,`Br`.`CoinPriceEnabled` AS `CoinPriceEnabledBr`,`Br`.`CoinPriceTop` AS `CoinPriceTopBr`,`Br`.`CoinPriceBtm` AS `CoinPriceBtmBr`,`Br`.`SellOrdersEnabled` AS `SellOrdersEnabledBr`,`Br`.`SellOrdersTop` AS `SellOrdersTopBr`,`Br`.`SellOrdersBtm` AS `SellOrdersBtmBr`,`Br`.`VolumeEnabled` AS `VolumeEnabledBr`,`Br`.`VolumeTop` AS `VolumeTopBr`,`Br`.`VolumeBtm` AS `VolumeBtmBr`,`Br`.`BuyCoin` AS `BuyCoinBr`,`Br`.`SendEmail` AS `SendEmailBr`,`Br`.`BTCAmount` AS `BTCAmountBr`,`Br`.`BuyType` AS `BuyTypeBr`,`Br`.`CoinOrder` AS `CoinOrderBr`,`Br`.`BuyCoinOffsetEnabled` AS `BuyCoinOffsetEnabledBr`,`Br`.`BuyCoinOffsetPct` AS `BuyCoinOffsetPctBr`,`Br`.`PriceTrendEnabled` AS `PriceTrendEnabledBr`,`Br`.`Price4Trend` AS `Price4TrendBr`,`Br`.`Price3Trend` AS `Price3TrendBr`,`Br`.`LastPriceTrend` AS `LastPriceTrendBr`,`Br`.`LivePriceTrend` AS `LivePriceTrendBr`,`Br`.`BuyPriceMinEnabled` AS `BuyPriceMinEnabledBr`,`Br`.`BuyPriceMin` AS `BuyPriceMinBr`,`Br`.`LimitToCoin` AS `LimitToCoinBr`,`Br`.`LimitToCoinID` AS `LimitToCoinIDBr`,`Br`.`AutoBuyCoinEnabled` AS `AutoBuyCoinEnabledBr`,`Br`.`AutoBuyCoinPct` AS `AutoBuyCoinPctBr`,`Br`.`BuyAmountOverrideEnabled` AS `BuyAmountOverrideEnabledBr`,`Br`.`BuyAmountOverride` AS `BuyAmountOverrideBr`,`Br`.`NewBuyPattern` AS `NewBuyPatternBr`,`Br`.`SellRuleFixed` AS `SellRuleFixedBr`,`Br`.`OverrideDailyLimit` AS `OverrideDailyLimitBr`,`Br`.`CoinPricePatternEnabled` AS `CoinPricePatternEnabledBr`,`Br`.`CoinPricePattern` AS `CoinPricePatternBr`,`Br`.`1HrChangeTrendEnabled` AS `1HrChangeTrendEnabledBr`,`Br`.`1HrChangeTrend` AS `1HrChangeTrendBr`,`Br`.`CoinPriceMatchPattern` AS `CoinPriceMatchPatternBr`,`Br`.`CoinPriceMatchID` AS `CoinPriceMatchIDBr`,`Br`.`CoinPricePatternID` AS `CoinPricePatternIDBr`,`Br`.`Coin1HrPatternID` AS `Coin1HrPatternIDBr`,`Br`.`BuyRisesInPrice` AS `BuyRisesInPriceBr`,`Br`.`DisableUntil` AS `DisableUntilBr`,`Br`.`OverrideDisableRule` AS `OverrideDisableRuleBr`,`Br`.`LimitBuyAmountEnabled` AS `LimitBuyAmountEnabledBr`,`Br`.`LimitBuyAmount` AS `LimitBuyAmountBr`,`Br`.`OverrideCancelBuyTimeEnabled` AS `OverrideCancelBuyTimeEnabledBr`,`Br`.`OverrideCancelBuyTimeMins` AS `OverrideCancelBuyTimeMinsBr`,`Br`.`LimitBuyTransactionsEnabled` AS `LimitBuyTransactionsEnabledBr`,`Br`.`LimitBuyTransactions` AS `LimitBuyTransactionsBr`,`Br`.`NoOfBuyModeOverrides` AS `NoOfBuyModeOverridesBr`,`Br`.`CoinModeOverridePriceEnabled` AS `CoinModeOverridePriceEnabledBr`,`Br`.`BuyModeActivate` AS `BuyModeActivateBr`,`Br`.`CoinMode` AS `CoinModeBr`,`Br`.`OverrideCoinAllocation` AS `OverrideCoinAllocationBr`,`Br`.`OneTimeBuyRule` AS `OneTimeBuyRuleBr`,`Br`.`LimitToBaseCurrency` AS `LimitToBaseCurrencyBr`,`Br`.`EnableRuleActivationAfterDip` AS `EnableRuleActivationAfterDipBr`,`Br`.`24HrPriceDipPct` AS `24HrPriceDipPctBr`,`Br`.`7DPriceDipPct` AS `7DPriceDipPctBr`,`Br`.`BuyAmountCalculationEnabled` AS `BuyAmountCalculationEnabledBr`,`Br`.`TotalPurchasesPerRule` AS `TotalPurchasesPerRuleBr`,`Br`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Br`.`RedirectPurchasesToSpreadID` AS `RedirectPurchasesToSpreadIDBR`,`Br`.`MultiSellRuleEnabled`,`Br`.`MultiSellRuleTemplateID`
,`Rls`.`ID` as `IDRls`, `Rls`.`UserID` as `UserIDRls` , `Rls`.`Enabled` as `ReduceLossEnabled`, `Rls`.`SellPct`, `Rls`.`OriginalPriceMultiplier`,`Rls`.`ReduceLossMaxCounter`,`Rls`.`HoursFlat` as HoursFlatRls,`Rls`.`ReduceLossMinsToCancel`
,`Nca`.`UserID` as `UserIDNca`,`Nca`.`USDTAlloc`,`Nca`.`BTCAlloc`,`Nca`.`ETHAlloc`,`Nca`.`PctOnLow`
,TIMESTAMPDIFF(MINUTE,date_add(`Ba`.`ActionDate`,Interval `Ba`.`MinsToCancelAction` Minute), now()) as `MinsRemaining`
, if (date_add(`Ba`.`ActionDate`,Interval `Ba`.`MinsToCancelAction` Minute)< now(),1,0) as DateADD
,date_add(`Ba`.`ActionDate`,Interval `Ba`.`MinsToCancelAction` Minute) as timeToCancel
,if (`Ba`.`TimeToCancel` > now(),1,0) as NewReadyToCancel
,UNIX_TIMESTAMP(now()) as TimeStampNow
,UNIX_TIMESTAMP(`Ba`.`TimeToCancel`) as TimeStampTimeToCancel
FROM `BittrexAction`  `Ba`
 join `User` `Us` on `Us`.`ID` = `Ba`.`UserID`
    join `UserConfig` `Uc` on `Uc`.`UserID` = `Ba`.`UserID`
    join `Coin` `Cn` on `Cn`.`ID` = `Ba`.`CoinID`
    join `CoinPrice` `Cp` on `Ba`.`CoinID` = `Cp`.`CoinID`
    join `Transaction` `Tr` on (`Tr`.`ID` = `Ba`.`TransactionID`) and (`Ba`.`Type` = `Tr`.`Type`)
    join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Ba`.`CoinID`
    left join `SellRules` `Sr` on `Sr`.`ID` = `Tr`.`FixSellRule`
    left join `BuyRules` `Br` on `Br`.`ID` = `Tr`.`BuyRule`
    left join `ReduceLossSettings` `Rls` on `Us`.`ID` = `Rls`.`UserID`
    left join `NewCoinAllocations` `Nca` on `Nca`.`UserID` =`Us`.`ID`;

    CREATE OR REPLACE VIEW `View5_SellCoins` as
    select `Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `Status`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRef`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`,`Tr`.`ReduceLossCounter`,`Tr`.`MultiSellRuleEnabled`,`Tr`.`StopBuyBack`,`Tr`.`OverrideReduceLoss`,`Tr`.`holdingAmount`,`Tr`.`SavingOverride`,`Tr`.`OverrideBittrexCancellation`
    ,`Cn`.`ID` AS `IDCn`,`Cn`.`Symbol` AS `Symbol`,`Cn`.`Name` AS `Name`,`Cn`.`BaseCurrency` AS `BaseCurrency`,`Cn`.`BuyCoin` AS `BuyCoin`,`Cn`.`CMCID` AS `CMCID`,`Cn`.`SecondstoUpdate` AS `SecondstoUpdate`,`Cn`.`Image` AS `Image`,`Cn`.`MinTradeSize` AS `MinTradeSize`,`Cn`.`CoinPrecision` AS `CoinPrecision`,`Cn`.`DoNotBuy` AS `DoNotBuy`
    ,`Cp`.`ID` AS `IDCp`,`Cp`.`CoinID` AS `CoinID6`,`Cp`.`LiveCoinPrice` AS `LiveCoinPrice`,`Cp`.`LastCoinPrice` AS `LastCoinPrice`,`Cp`.`Price3` AS `Price3`,`Cp`.`Price4` AS `Price4`,`Cp`.`Price5` AS `Price5`,`Cp`.`LastUpdated` AS `LastUpdated`,(((`Cp`.`LiveCoinPrice` - `Cp`.`LastCoinPrice`) / `Cp`.`LastCoinPrice`) * 100) AS `CoinPricePctChange`
    ,`Cmc`.`ID` AS `IDCmc`,`Cmc`.`CoinID` AS `CoinID2`,`Cmc`.`LiveMarketCap` AS `LiveMarketCap`,`Cmc`.`LastMarketCap` AS `LastMarketCap`,(((`Cmc`.`LiveMarketCap` - `Cmc`.`LastMarketCap`) / `Cmc`.`LastMarketCap`) * 100) AS `MarketCapPctChange`
    ,`Cbo`.`ID` AS `IDCbo`,`Cbo`.`CoinID` AS `CoinID3`,`Cbo`.`LiveBuyOrders` AS `LiveBuyOrders`,`Cbo`.`LastBuyOrders` AS `LastBuyOrders`,(((`Cbo`.`LiveBuyOrders` - `Cbo`.`LastBuyOrders`) / `Cbo`.`LastBuyOrders`) * 100) AS `BuyOrdersPctChange`,`Cv`.`ID` AS `IDCv`,`Cv`.`CoinID` AS `CoinID4`,`Cv`.`LiveVolume` AS `LiveVolume`,`Cv`.`LastVolume` AS `LastVolume`,(((`Cv`.`LiveVolume` - `Cv`.`LastVolume`) / `Cv`.`LastVolume`) * 100) AS `VolumePctChange`
    ,`Cpc`.`ID` AS `IDCpc`,`Cpc`.`CoinID` AS `CoinID5`,`Cpc`.`Live1HrChange` AS `Live1HrChange`,`Cpc`.`Last1HrChange` AS `Last1HrChange`,`Cpc`.`Live24HrChange` AS `Live24HrChange`,`Cpc`.`Last24HrChange` AS `Last24HrChange`,`Cpc`.`Live7DChange` AS `Live7DChange`,`Cpc`.`Last7DChange` AS `Last7DChange`,`Cpc`.`1HrChange3` AS `1HrChange3`,`Cpc`.`1HrChange4` AS `1HrChange4`,`Cpc`.`1HrChange5` AS `1HrChange5`
    ,(((`Cp`.`LiveCoinPrice`- `Cpc`.`Live1HrChange`) / `Cpc`.`Live1HrChange`) * 100) AS `Hr1ChangePctChange`
    ,(((`Cp`.`LiveCoinPrice` - `Cpc`.`Live24HrChange`) / `Cpc`.`Live24HrChange`) * 100) AS `Hr24ChangePctChange`
    ,(((`Cp`.`LiveCoinPrice` - `Cpc`.`Live7DChange`) / `Cpc`.`Live7DChange`) * 100) AS `D7ChangePctChange`
    ,`Cso`.`ID` AS `IDCso`,`Cso`.`CoinID` AS `CoinID7`,`Cso`.`LiveSellOrders` AS `LiveSellOrders`,`Cso`.`LastSellOrders` AS `LastSellOrders`,(((`Cso`.`LiveSellOrders` - `Cso`.`LastSellOrders`) / `Cso`.`LastSellOrders`) * 100) AS `SellOrdersPctChange`
    ,if(((`Cp`.`LiveCoinPrice` - `Cp`.`LastCoinPrice`) > 0),1,if(((`Cp`.`LiveCoinPrice` - `Cp`.`LastCoinPrice`) < 0),-(1),0)) AS `LivePriceTrend`
    ,if(((`Cp`.`LastCoinPrice` - `Cp`.`Price3`) > 0),1,if(((`Cp`.`LastCoinPrice` - `Cp`.`Price3`) < 0),-(1),0)) AS `LastPriceTrend`
    ,if(((`Cp`.`Price3` - `Cp`.`Price4`) > 0),1,if(((`Cp`.`Price3` - `Cp`.`Price4`) < 0),-(1),0)) AS `Price3Trend`
    ,if(((`Cp`.`Price4` - `Cp`.`Price5`) > 0),1,if(((`Cp`.`Price4` - `Cp`.`Price5`) < 0),-(1),0)) AS `Price4Trend`
    ,if(((`Cpc`.`Live1HrChange` - `Cpc`.`Last1HrChange`) > 0),1,if(((`Cpc`.`Live1HrChange` - `Cpc`.`Last1HrChange`) < 0),-(1),0)) AS `1HrPriceChangeLive`
    ,if(((`Cpc`.`Last1HrChange` - `Cpc`.`1HrChange3`) > 0),1,if(((`Cpc`.`Last1HrChange` - `Cpc`.`1HrChange3`) < 0),-(1),0)) AS `1HrPriceChangeLast`
    ,if(((`Cpc`.`1HrChange3` - `Cpc`.`1HrChange4`) > 0),1,if(((`Cpc`.`1HrChange3` - `Cpc`.`1HrChange4`) < 0),-(1),0)) AS `1HrPriceChange3`
    ,if(((`Cpc`.`1HrChange4` - `Cpc`.`1HrChange5`) > 0),1,if(((`Cpc`.`1HrChange4` - `Cpc`.`1HrChange5`) < 0),-(1),0)) AS `1HrPriceChange4`
    ,`Uc`.`UserID` AS `UserID2`,`Uc`.`APIKey` AS `APIKey`,`Uc`.`APISecret` AS `APISecret`,`Uc`.`EnableDailyBTCLimit` AS `EnableDailyBTCLimit`,`Uc`.`EnableTotalBTCLimit` AS `EnableTotalBTCLimit`,`Uc`.`DailyBTCLimit` AS `DailyBTCLimit`,`Uc`.`TotalBTCLimit` AS `TotalBTCLimit`,`Uc`.`BTCBuyAmount` AS `BTCBuyAmount`,`Uc`.`CoinSellOffsetEnabled` AS `CoinSellOffsetEnabled2`,`Uc`.`CoinSellOffsetPct` AS `CoinSellOffsetPct2`,`Uc`.`BaseCurrency` AS `BaseCurrency2`,`Uc`.`NoOfCoinPurchase` AS `NoOfCoinPurchase`,`Uc`.`TimetoCancelBuy` AS `TimetoCancelBuy`,`Uc`.`TimeToCancelBuyMins` AS `TimeToCancelBuyMins`,`Uc`.`KEK` AS `KEK`,`Uc`.`MinsToPauseAlert` AS `MinsToPauseAlert`,`Uc`.`LowPricePurchaseEnabled` AS `LowPricePurchaseEnabled`,`Uc`.`NoOfPurchases` AS `NoOfPurchases2`,`Uc`.`PctToPurchase` AS `PctToPurchase`,`Uc`.`TotalRisesInPrice` AS `TotalRisesInPrice`,`Uc`.`TotalRisesInPriceSell` AS `TotalRisesInPriceSell`,`Uc`.`ReservedUSDT` AS `ReservedUSDT`,`Uc`.`ReservedBTC` AS `ReservedBTC`,`Uc`.`ReservedETH` AS `ReservedETH`,`Uc`.`TotalProfitPauseEnabled` AS `TotalProfitPauseEnabled`,`Uc`.`TotalProfitPause` AS `TotalProfitPause`,`Uc`.`PauseRulesEnabled` AS `PauseRulesEnabled`,`Uc`.`PauseRules` AS `PauseRules`,`Uc`.`PauseHours` AS `PauseHours`,`Uc`.`MergeAllCoinsDaily` AS `MergeAllCoinsDaily`,`Uc`.`MarketDropStopEnabled` AS `MarketDropStopEnabled`,`Uc`.`MarketDropStopPct` AS `MarketDropStopPct`,`Uc`.`SellAllCoinsEnabled` AS `SellAllCoinsEnabled`,`Uc`.`SellAllCoinsPct` AS `SellAllCoinsPct`,`Uc`.`CoinModeEmails` AS `CoinModeEmails`,`Uc`.`CoinModeEmailsSell` AS `CoinModeEmailsSell`,`Uc`.`CoinModeMinsToCancelBuy` AS `CoinModeMinsToCancelBuy`,`Uc`.`PctToSave` AS `PctToSave`,`Uc`.`SplitBuyAmounByPctEnabled` AS `SplitBuyAmounByPctEnabled`,`Uc`.`NoOfSplits` AS `NoOfSplits`,`Uc`.`SaveResidualCoins` AS `SaveResidualCoins`,`Uc`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Uc`.`SpreadBetRuleID` AS `SpreadBetRuleID2`,`Uc`.`MinsToPauseAfterPurchase` AS `MinsToPauseAfterPurchase`,`Uc`.`LowMarketModeEnabled` AS `LowMarketModeEnabled`,`Uc`.`LowMarketModeDate` AS `LowMarketModeDate`,`Uc`.`AutoMergeSavings` AS `AutoMergeSavings`,`Uc`.`AllBuyBackAsOverride` AS `AllBuyBackAsOverride`,`Uc`.`TotalPurchasesPerCoin` AS `TotalPurchasesPerCoin`,`Uc`.`SellSavingsEnabled`,`Uc`.`HoldCoinForBuyOut`,`Uc`.`CoinForBuyOutPct`,`Uc`.`PctOfAuto`,`Uc`.`PctOfAutoBuyBack`,`Uc`.`PctOfAutoReduceLoss`
    ,`Tr`.`CoinPrice`*`Tr`.`Amount` as OriginalPrice,
      ((`Tr`.`CoinPrice`*`Tr`.`Amount`)/100)*0.28 as CoinFee,
      `Cp`.`LiveCoinPrice`*`Tr`.`Amount` as LivePrice
      ,(`Cp`.`LiveCoinPrice`*`Tr`.`Amount`)-(`Tr`.`CoinPrice`*`Tr`.`Amount`)-(((`Tr`.`CoinPrice`*`Tr`.`Amount`)/100)*0.28) as ProfitUSD
      ,(((`Cp`.`LiveCoinPrice`*`Tr`.`Amount`)-(`Tr`.`CoinPrice`*`Tr`.`Amount`)-(((`Tr`.`CoinPrice`*`Tr`.`Amount`)/100)*0.28)) /(`Tr`.`CoinPrice`*`Tr`.`Amount`))*100 as ProfitPct
      ,TimeStampDiff(MINUTE, `Tr`.`DelayCoinSwapUntil`, now()) as `minsToDelay`
      ,TIMESTAMPDIFF(MINUTE, `Tr`.`OrderDate`, Now()) as MinsFromBuy
      ,if (`DelayCoinSwapUntil`< now(), 0,1) as CoinSwapDelayed
      ,`Sbr`.`ID` as `IDSbr`, `Sbr`.`Name` as `SpreadBetRuleName`, `Sbr`.`UserID` as `UserIDSbr`
      ,`Bi`.`ID` as `IDBi`, `Bi`.`CoinID` as `CoinIDBi`, `Bi`.`TopPrice`, `Bi`.`LowPrice`, `Bi`.`Difference`, `Bi`.`NoOfSells`
      ,`Rls`.`ID` as `IDRls`, `Rls`.`UserID` as `UserIDRls` , `Rls`.`Enabled`, `Rls`.`SellPct`, `Rls`.`OriginalPriceMultiplier`,`Rls`.`ReduceLossMaxCounter`,`Rls`.`HoursFlat` as HoursFlatRls,`Rls`.`HoursFlatAutoEnabled`,`Rls`.`ReduceLossMinsToCancel`
      , `Pds`.`ID` as `IDPds`, `Pds`.`BuyRuleID` as `BuyRuleIDPds`, `Pds`.`PriceDipEnabled`, `Pds`.`HoursFlat`, `Pds`.`DipStartTime`
      ,`Pdcs`.`ID` as `IDPdcs`, `Pdcs`.`CoinID` as `CoinIDPdcs`, `Pdcs`.`PriceDipEnabled` as `PriceDipEnabledPdcs`, `Pdcs`.`HoursFlat` as `HoursFlatPdcs`, `Pdcs`.`DipStartTime` as `DipStartTimePdcs`, `Pdcs`.`HoursFlatLow` as `HoursFlatLowPdcs`, `Pdcs`.`HoursFlatHigh` as `HoursFlatHighPdcs`, `Pdcs`.`MaxHoursFlat`
      ,avgMaxPrice(`Cn`.`ID`,20) as `MaxPriceFromHigh`, ((`Cp`.`LiveCoinPrice`- avgMaxPrice(`Cn`.`ID`,20))/avgMaxPrice(`Cn`.`ID`,20))*100 as `PctFromLiveToHigh`
      ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
      , `Us`.`DisableUntil`
      ,`Cpe`.`Hr1Top`,`Cpe`.`Hr1Bottom`
      ,`Caa`.`ID` as `CaaID`, `Caa`.`CoinID` as `CaaCoinID`, `Caa`.`Offset` as `CaaOffset`, `Caa`.`SellOffset` as `CaaSellOffset`, `Caa`.`MinsToCancelBuy` as `CaaMinsToCancelBuy`, `Caa`.`MinsToCancelSell`as `CaaMinsToCancelSell`
    from ((((((((`Transaction` `Tr`
      join `Coin` `Cn` on((`Cn`.`ID` = `Tr`.`CoinID`)))
      join `CoinAskPrice` `Cp` on((`Cp`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinBuyOrders` `Cbo` on((`Cbo`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinMarketCap` `Cmc` on((`Cmc`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinSellOrders` `Cso` on((`Cso`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinVolume` `Cv` on((`Cv`.`CoinID` = `Tr`.`CoinID`)))
      join `CoinPctChange` `Cpc` on((`Cpc`.`CoinID` = `Tr`.`CoinID`)))
      join `UserConfig` `Uc` on((`Uc`.`UserID` = `Tr`.`UserID`)))
      Left join `SpreadBetRules` `Sbr` on `Sbr`.`ID` = `Tr`.`SpreadBetRuleID`
      join `BounceIndex` `Bi` on `Bi`.`CoinID` = `Tr`.`CoinID`
      Left Join `ReduceLossSettings` `Rls` on `Rls`.`UserID` = `Tr`.`UserID`
      left Join  `PriceDipStatus` `Pds` on `Pds`.`BuyRuleID` = `Tr`.`BuyRule`
      join `PriceDipCoinStatus` `Pdcs` on `Pdcs`.`CoinID` =  `Cn`.`ID`
      join `User` `Us` on `Us`.`ID` = `Uc`.`UserID`
      left join `CoinPriceExtra` `Cpe` on `Cpe`.`CoinID` = `Tr`.`CoinID`
      left Join `CoinAutoActions` `Caa` on `Caa`.`CoinID` = `Cn`.`ID`;

    CREATE OR REPLACE VIEW `View6_TrackingSellCoins` as
    SELECT `Tsc`.`ID` as `IDTsc`, `Tsc`.`CoinPrice` as `CoinPriceTsc`, `Tsc`.`TrackDate`, `Tsc`.`UserID` as `UserIDTsc`, `Tsc`.`NoOfRisesInPrice`, `Tsc`.`TransactionID` as `TransactionIDTsc`, `Tsc`.`Status` as `StatusTsc`, `Tsc`.`SellCoin`, `Tsc`.`SendEmail`, `Tsc`.`CoinSellOffsetEnabled`, `Tsc`.`CoinSellOffsetPct`, `Tsc`.`TrackStartDate`, `Tsc`.`SellFallsInPrice` as `SellFallsInPriceSr`, `Tsc`.`BaseSellPrice`, `Tsc`.`LastPrice`, `Tsc`.`Type` as `TrackingType`, `Tsc`.`OriginalSellPrice`,`Tsc`.`TrackingCount`
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
    , `Uc`.`TotalPurchasesPerCoin`,`Uc`.`BuyBackEnabled`
    ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
    , `Us`.`DisableUntil`
    ,TIMESTAMPDIFF(MINUTE,`TrackDate`, Now()) as MinsFromDate,(`LiveCoinPrice`*`Amount`)-(`Tr`.`CoinPrice`*`Amount`) as `ProfitUSD`, ((`LiveCoinPrice`*`Amount`)/100)*0.28 as `Fee`
            ,(((`LiveCoinPrice`*`Amount`)-(`Tr`.`CoinPrice`*`Amount`))/(`Tr`.`CoinPrice`*`Amount`))*100`PctProfit`
     , ((`LiveCoinPrice`*`Amount`)-(`Tr`.`CoinPrice` * `Amount`))/ (`Tr`.`CoinPrice` * `Amount`) * 100 as `OgPctProfit`
    , `Tr`.`CoinPrice` * `Amount` as `OriginalPurchasePrice`
       ,TIMESTAMPDIFF(MINUTE,`TrackStartDate`,Now()) as MinsFromStart
    ,(`LiveCoinPrice`*`Amount`) as LiveTotalPrice
    ,`Sr`.`ID` as `IDSr`,  `Sr`.`RuleName` , `Sr`.`UserID` as `UserIDSr` , `Sr`.`SellCoin` as `SellCoinSr`, `Sr`.`SendEmail` as `SendEmailSr`, `Sr`.`BuyOrdersEnabled` , `Sr`.`BuyOrdersTop` , `Sr`.`BuyOrdersBtm` , `Sr`.`MarketCapEnabled` , `Sr`.`MarketCapTop` , `Sr`.`MarketCapBtm` , `Sr`.`1HrChangeEnabled` , `Sr`.`1HrChangeTop` , `Sr`.`1HrChangeBtm` , `Sr`.`24HrChangeEnabled` , `Sr`.`24HrChangeTop` , `Sr`.`24HrChangeBtm` , `Sr`.`7DChangeEnabled` , `Sr`.`7DChangeTop` , `Sr`.`7DChangeBtm` , `Sr`.`ProfitPctEnabled` , `Sr`.`ProfitPctTop` , `Sr`.`ProfitPctBtm` , `Sr`.`CoinPriceEnabled` , `Sr`.`CoinPriceTop` , `Sr`.`CoinPriceBtm` , `Sr`.`SellOrdersEnabled` , `Sr`.`SellOrdersTop` , `Sr`.`SellOrdersBtm` , `Sr`.`VolumeEnabled` , `Sr`.`VolumeTop` , `Sr`.`VolumeBtm` , `Sr`.`CoinOrder` , `Sr`.`SellCoinOffsetEnabled` , `Sr`.`SellCoinOffsetPct` , `Sr`.`SellPriceMinEnabled` , `Sr`.`SellPriceMin` , `Sr`.`LimitToCoin` , `Sr`.`LimitToCoinID` , `Sr`.`AutoSellCoinEnabled` , `Sr`.`AutoSellCoinPct` , `Sr`.`SellPatternEnabled` , `Sr`.`SellPattern` , `Sr`.`LimitToBuyRule` , `Sr`.`CoinPricePatternEnabled` , `Sr`.`CoinPricePattern` , `Sr`.`CoinPriceMatchNameID` , `Sr`.`CoinPricePatternNameID` , `Sr`.`CoinPrice1HrPatternNameID` , `Sr`.`SellFallsInPrice` , `Sr`.`CoinModeRule` , `Sr`.`CoinSwapEnabled` , `Sr`.`CoinSwapAmount` , `Sr`.`NoOfCoinSwapsPerWeek` , `Sr`.`MergeCoinEnabled` , `Sr`.`ReEnableBuyRuleEnabled` , `Sr`.`ReEnableBuyRule`
    ,`Sr`.`OverrideBuyBackAmount`,`Sr`.`OverrideBuyBackSaving`
    FROM `TrackingSellCoins` `Tsc`
    Join `Transaction` `Tr` on `Tr`.`ID` = `Tsc`.`TransactionID`
     Join `CoinAskPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
     join `UserConfig` `Uc` on `Uc`.`UserID` = `Tr`.`UserID`
     join `User` `Us` on `Us`.`ID` = `Tr`.`UserID`
     join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
     Left Join `SellRules` `Sr` on `Sr`.`ID` = `Tr`.`FixSellRule`;


 CREATE OR REPLACE VIEW `View7_SpreadBetSell` as
SELECT `Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `Status`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRef`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
, `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
, `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,`Sbs`. `ID`, `Sbs`. `SpreadBetRuleID` as `SpreadBetRuleIDSbs`, `Sbs`. `Hr1BuyPrice`, `Sbs`. `Hr24BuyPrice`, `Sbs`. `D7BuyPrice`, `Sbs`. `NextReviewDate`, `Sbs`. `PctProfitSell`, `Sbs`. `NoOfTransactions`, `Sbs`. `LowestPctProfit`, `Sbs`. `AvgTimeToSell`, `Sbs`. `Enabled`, `Sbs`. `Hr1BuyEnable`, `Sbs`. `Hr1BuyDisable`, `Sbs`. `Hr24andD7StartPrice`, `Sbs`. `Month6TotalPrice`, `Sbs`. `AllTimeTotalPrice`, `Sbs`. `Hr1EnableStartPrice`, `Sbs`. `BuyFallsinPrice`, `Sbs`. `SellRaisesInPrice`, `Sbs`. `MinsToCancel`, `Sbs`. `CalculatedMinsToCancel`, `Sbs`. `CalculatedRisesInPrice`, `Sbs`. `CalculatedFallsinPrice`, `Sbs`. `AutoBuyBackSell`
,`Ath`. `ID` as `IDAth`, `Ath`. `CoinID` as `CoinIDAth`, `Ath`. `HighLow` as `HighAth`, `Ath`. `Price` as `PriceAth`
,`Atl`. `ID` as `IDAtl`, `Atl`. `CoinID` as `CoinIDAtl`, `Atl`. `HighLow` as `LowAtl`, `Atl`. `Price` as `PriceAtl`
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
,`M6p`.`ID` as `IDM6p`, `M6p`.`CoinID` as `CoinIDM6p`, `M6p`.`MaxPrice` as `SixMonthHighPrice`, `M6p`.`Month`, `M6p`.`Year`
,`Bbs`.`ID` as `IDBbs`, `Bbs`.`CoinID` as `CoinIDBbs`, `Bbs`.`LastPriceChange`, `Bbs`.`Min15PriceChange`, `Bbs`.`Min30PriceChange`, `Bbs`.`Min45PriceChange`, `Bbs`.`Min75PriceChange`, `Bbs`.`OneHrPriceChange`, `Bbs`.`Twenty4HrPriceChange`, `Bbs`.`MarketPriceChange`, `Bbs`.`Days7PriceChange`
,`Sbst`.`ID` as `IDSbst`, `Sbst`.`TransactionID` as `TransactionIDSbst`, `Sbst`.`SBTransactionID`, `Sbst`.`SellPct`
,`Ba`.`ID` as `IDBa`, `Ba`.`CoinID`as `CoinID6`, `Ba`.`TransactionID` as `TransactionIDBa`, `Ba`.`UserID` AS `UserIDBa`, `Ba`.`Type` as `TypeBa`, `Ba`.`BittrexRef` as `BittrexRefBa`, `Ba`.`ActionDate`, `Ba`.`CompletionDate` as `CompletionDateBa`, `Ba`.`Status` as `StatusBa`, `Ba`.`SellPrice`, `Ba`.`RuleID`, `Ba`.`RuleIDSell`, `Ba`.`QuantityFilled`, `Ba`.`MultiplierPrice`, `Ba`.`BuyBack`, `Ba`.`OldBuyBackTransID`, `Ba`.`ResidualAmount`
    FROM `Transaction` `Tr`
    join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
    join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Tr`.`CoinID`
    join `CoinAskPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
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
     join `SpreadBetRules` `Sbr` on `Sbr`.`ID` = `Tr`.`SpreadBetRuleID`
     left join `AllTimeHighLow` `Ath` on `Ath`.`CoinID` = `Tr`.`CoinID` and `Ath`.`HighLow` = 'High'
     left join `AllTimeHighLow` `Atl` on `Atl`.`CoinID` = `Tr`.`CoinID` and `Atl`.`HighLow` = 'Low'
     left join `MonthlyMaxPrices` `M6p` on `Tr`.`CoinID` =`M6p`.`CoinID` and `Month` = Month(DATE_SUB(now(), INTERVAL 6 MONTH)) and `Year` = Year(DATE_SUB(now(), INTERVAL 6 MONTH))
     join `BearBullStats` `Bbs` on `Bbs`.`CoinID` =`Tr`.`CoinID`
     JOIN `SpreadBetSellTarget` `Sbst` on `Sbst`.`TransactionID` = `Tr`.`ID`
     Left Join `BittrexAction` `Ba` on `Ba`.`TransactionID` = `Tr`.`ID` and `Tr`.`Type` = `Ba`.`Type`;

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
 , `Uc`.`TotalPurchasesPerCoin`,`Uc`.`RebuySavingsEnabled`
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
 SELECT `Bb`.`ID` as `IDBb`, `Bb`.`TransactionID` as `TransactionIDBb`, `Bb`.`Quantity`, `Bb`.`SellPrice` as `SellPriceBb`, `Bb`.`Status` as `StatusBb`, `Bb`.`ProfitMultiply`, `Bb`.`NoOfRaisesInPrice`, `Bb`.`BuyBackPct`, `Bb`.`MinsToCancel`, `Bb`.`DateTimeAdded`, `Bb`.`DelayTime`, `Bb`.`CoinPrice` as `CoinPriceBB`,`Bb`.`USDBuyBackAmount`,`Bb`.`MinsToCancel` as bbMinsToCancel,`Bb`.`DateClosed`
 ,`Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinIDTr`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `StatusTr`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletioanDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRefTr`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`
 ,`Ba`.`ID` as `IDBa`, `Ba`.`CoinID`as `CoinID4`, `Ba`.`TransactionID`, `Ba`.`UserID` AS `UserIDBa`, `Ba`.`Type` as `TypeBa`, `Ba`.`BittrexRef` as `BittrexRefBa`, `Ba`.`ActionDate`, `Ba`.`CompletionDate` as `CompletionDateBa`, `Ba`.`Status` as `StatusBa`, `Ba`.`SellPrice`, `Ba`.`RuleID`, `Ba`.`RuleIDSell`, `Ba`.`QuantityFilled`, `Ba`.`MultiplierPrice`, `Ba`.`BuyBack`, `Ba`.`OldBuyBackTransID`, `Ba`.`ResidualAmount`
 , `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinID2`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
 ,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
 , `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
 , `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
 , `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
 , `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
 , `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
 , `Uc`.`TotalPurchasesPerCoin`,`Uc`.`SaveMode`,`Uc`.`BuyBackHoursFlatTarget`,`Uc`.`PctOfAuto`,`Uc`.`BuyBackHoursFlatAutoEnabled`,`Uc`.`PctOfAutoBuyBack`,`Uc`.`PctOfAutoReduceLoss`,`Uc`.`BuyBackMinsToCancel`,`Uc`.`BuyBackAutoPct`
 ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
 , `Us`.`DisableUntil`
 ,`Bbs`.`ID`, `Bbs`.`CoinID`, `Bbs`.`LastPriceChange`, `Bbs`.`Min15PriceChange`, `Bbs`.`Min30PriceChange`, `Bbs`.`Min45PriceChange`, `Bbs`.`Min75PriceChange`, `Bbs`.`OneHrPriceChange`, `Bbs`.`Twenty4HrPriceChange`, `Bbs`.`MarketPriceChange`, `Bbs`.`Days7PriceChange`
 ,`Pdcs`.`ID` as `IDPdcs`, `Pdcs`.`CoinID` as `CoinIDPdcs`, `Pdcs`.`PriceDipEnabled` as `PriceDipEnabledPdcs`, `Pdcs`.`HoursFlat` as `HoursFlatPdcs`, `Pdcs`.`DipStartTime` as `DipStartTimePdcs`, `Pdcs`.`HoursFlatLow` as `HoursFlatLowPdcs`, `Pdcs`.`HoursFlatHigh` as `HoursFlatHighPdcs`, `Pdcs`.`MaxHoursFlat`
 ,`Cpc`.`ID` AS `IDCpc`,`Cpc`.`CoinID` AS `CoinID5`,`Cpc`.`Live1HrChange` AS `Live1HrChange`,`Cpc`.`Last1HrChange` AS `Last1HrChange`,`Cpc`.`Live24HrChange` AS `Live24HrChange`,`Cpc`.`Last24HrChange` AS `Last24HrChange`,`Cpc`.`Live7DChange` AS `Live7DChange`,`Cpc`.`Last7DChange` AS `Last7DChange`,`Cpc`.`1HrChange3` AS `1HrChange3`,`Cpc`.`1HrChange4` AS `1HrChange4`,`Cpc`.`1HrChange5` AS `1HrChange5`
 ,(((`Cp`.`LiveCoinPrice` - `Cpc`.`Live1HrChange`) / `Cpc`.`Live1HrChange`) * 100) AS `Hr1ChangePctChange`
 ,(((`Cp`.`LiveCoinPrice` - `Cpc`.`Last24HrChange` ) / `Cpc`.`Last24HrChange` ) * 100) AS `Hr24ChangePctChange`
 ,(((`Cp`.`LiveCoinPrice` - `Cpc`.`Live7DChange` ) / `Cpc`.`Live7DChange` ) * 100) AS `D7ChangePctChange`
 ,if(((`Cpc`.`Live1HrChange` - `Cpc`.`Last1HrChange`) > 0),1,if(((`Cpc`.`Live1HrChange` - `Cpc`.`Last1HrChange`) < 0),-(1),0)) AS `1HrPriceChangeLive`
  ,if(((`Cpc`.`Last1HrChange` - `Cpc`.`1HrChange3`) > 0),1,if(((`Cpc`.`Last1HrChange` - `Cpc`.`1HrChange3`) < 0),-(1),0)) AS `1HrPriceChangeLast`
  ,if(((`Cpc`.`1HrChange3` - `Cpc`.`1HrChange4`) > 0),1,if(((`Cpc`.`1HrChange3` - `Cpc`.`1HrChange4`) < 0),-(1),0)) AS `1HrPriceChange3`
  ,if(((`Cpc`.`1HrChange4` - `Cpc`.`1HrChange5`) > 0),1,if(((`Cpc`.`1HrChange4` - `Cpc`.`1HrChange5`) < 0),-(1),0)) AS `1HrPriceChange4`
  ,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
  , `Cn`.`DoNotBuy`
  ,`Dcp`.`ID` as `IDDcp`, `Dcp`.`CoinID` as `CoinIDDcp`, `Dcp`.`UserID` as `UserIDDcp`,`Dcp`.`DelayTime` as `DelayTimeDcp`
  ,if(`Bb`.`DelayTime`<now(), 0,1) as BBRuleDisabled
  , timestampdiff(MINUTE,now(),`Bb`.`DelayTime`) as MinsUntilEnable
  ,`Caa`.`ID` as `CaaID`, `Caa`.`CoinID` as `CaaCoinID`, `Caa`.`Offset` as `CaaOffset`, `Caa`.`MinsToCancelBuy` as `CaaMinsToCancelBuy`, `Caa`.`MinsToCancelSell`as `CaaMinsToCancelSell`
  , TIMESTAMPDIFF(HOUR, `Bb`.`DateClosed`, now()) as hoursSinceClosed
 FROM `BuyBack` `Bb`
 join `Transaction` `Tr` on `Tr`.`ID` = `Bb`.`TransactionID`
 join `BittrexAction` `Ba` on `Ba`.`TransactionID` = `Tr`.`ID` and `Ba`.`Type` in ('Sell','SpreadSell')
 join `CoinBidPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
 join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
 join `UserConfig` `Uc` on `Uc`.`UserID` = `Tr`.`UserID`
 join `User` `Us` on `Us`.`ID` = `Tr`.`UserID`
 join `BearBullStats` `Bbs` on `Bbs`.`CoinID` =`Tr`.`CoinID`
 join `CoinPctChange` `Cpc` on((`Cpc`.`CoinID` = `Tr`.`CoinID`))
 Left join `PriceDipCoinStatus` `Pdcs` on `Pdcs`.`CoinID` =  `Tr`.`CoinID`
 left join `DelayCoinPurchase` `Dcp` on `Dcp`.`CoinID` = `Tr`.`CoinID` and `Dcp`.`UserID` = `Tr`.`UserID`
 left Join `CoinAutoActions` `Caa` on `Caa`.`CoinID` = `Cn`.`ID`;

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
select `Br`.`ID` AS `RuleID`,`Br`.`RuleName` AS `RuleName`,`Br`.`UserID` AS `UserID`,`Br`.`BuyOrdersEnabled` AS `BuyOrdersEnabled`,`Br`.`BuyOrdersTop` AS `BuyOrdersTop`,`Br`.`BuyOrdersBtm` AS `BuyOrdersBtm`,`Br`.`MarketCapEnabled` AS `MarketCapEnabled`,`Br`.`MarketCapTop` AS `MarketCapTop`,`Br`.`MarketCapBtm` AS `MarketCapBtm`,`Br`.`1HrChangeEnabled` AS `1HrChangeEnabled`,`Br`.`1HrChangeTop` AS `1HrChangeTop`,`Br`.`1HrChangeBtm` AS `1HrChangeBtm`,`Br`.`24HrChangeEnabled` AS `24HrChangeEnabled`,`Br`.`24HrChangeTop` AS `24HrChangeTop`,`Br`.`24HrChangeBtm` AS `24HrChangeBtm`,`Br`.`7DChangeEnabled` AS `7DChangeEnabled`,`Br`.`7DChangeTop` AS `7DChangeTop`,`Br`.`7DChangeBtm` AS `7DChangeBtm`,`Br`.`CoinPriceEnabled` AS `CoinPriceEnabled`,`Br`.`CoinPriceTop` AS `CoinPriceTop`,`Br`.`CoinPriceBtm` AS `CoinPriceBtm`,`Br`.`SellOrdersEnabled` AS `SellOrdersEnabled`,`Br`.`SellOrdersTop` AS `SellOrdersTop`,`Br`.`SellOrdersBtm` AS `SellOrdersBtm`,`Br`.`VolumeEnabled` AS `VolumeEnabled`,`Br`.`VolumeTop` AS `VolumeTop`,`Br`.`VolumeBtm` AS `VolumeBtm`,`Br`.`BuyCoin` AS `BuyCoin`,`Br`.`SendEmail` AS `SendEmail`,`Br`.`BTCAmount` AS `BTCAmount`,`Br`.`BuyType` AS `BuyType`,`Br`.`CoinOrder` AS `CoinOrder`,`Br`.`BuyCoinOffsetEnabled` AS `BuyCoinOffsetEnabled`,`Br`.`BuyCoinOffsetPct` AS `BuyCoinOffsetPct`,`Br`.`PriceTrendEnabled` AS `PriceTrendEnabled`,`Br`.`Price4Trend` AS `Price4Trend`,`Br`.`Price3Trend` AS `Price3Trend`,`Br`.`LastPriceTrend` AS `LastPriceTrend`,`Br`.`LivePriceTrend` AS `LivePriceTrend`,`Br`.`BuyPriceMinEnabled` AS `BuyPriceMinEnabled`,`Br`.`BuyPriceMin` AS `BuyPriceMin`,`Br`.`LimitToCoin` AS `LimitToCoin`,`Br`.`LimitToCoinID` AS `LimitToCoinID`,`Br`.`AutoBuyCoinEnabled` AS `AutoBuyCoinEnabled`,`Br`.`AutoBuyCoinPct` AS `AutoBuyCoinPct`,`Br`.`BuyAmountOverrideEnabled` AS `BuyAmountOverrideEnabled`,`Br`.`BuyAmountOverride` AS `BuyAmountOverride`,`Br`.`NewBuyPattern` AS `NewBuyPattern`,`Br`.`SellRuleFixed` AS `SellRuleFixed`,`Br`.`OverrideDailyLimit` AS `OverrideDailyLimit`,`Br`.`CoinPricePatternEnabled` AS `CoinPricePatternEnabled`,`Br`.`CoinPricePattern` AS `CoinPricePattern`,`Br`.`1HrChangeTrendEnabled` AS `1HrChangeTrendEnabled`,`Br`.`1HrChangeTrend` AS `1HrChangeTrend`,`Br`.`CoinPriceMatchPattern` AS `CoinPriceMatchPattern`,`Br`.`CoinPriceMatchID` AS `CoinPriceMatchID`,`Br`.`CoinPricePatternID` AS `CoinPricePatternID`,`Br`.`Coin1HrPatternID` AS `Coin1HrPatternID`,`Br`.`BuyRisesInPrice` AS `BuyRisesInPrice`,`Br`.`DisableUntil` AS `DisableUntil`,`Br`.`OverrideDisableRule` AS `OverrideDisableRule`,`Br`.`LimitBuyAmountEnabled` AS `LimitBuyAmountEnabled`,`Br`.`LimitBuyAmount` AS `LimitBuyAmount`,`Br`.`OverrideCancelBuyTimeEnabled` AS `OverrideCancelBuyTimeEnabled`,`Br`.`OverrideCancelBuyTimeMins` AS `OverrideCancelBuyTimeMins`,`Br`.`LimitBuyTransactionsEnabled` AS `LimitBuyTransactionsEnabled`,`Br`.`LimitBuyTransactions` AS `LimitBuyTransactions`,`Br`.`NoOfBuyModeOverrides` AS `NoOfBuyModeOverrides`,`Br`.`CoinModeOverridePriceEnabled` AS `CoinModeOverridePriceEnabled`,`Br`.`BuyModeActivate` AS `BuyModeActivate`,`Br`.`CoinMode` AS `CoinMode`,`Br`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Br`.`OneTimeBuyRule` AS `OneTimeBuyRule`,`Br`.`LimitToBaseCurrency` AS `LimitToBaseCurrency`,`Br`.`EnableRuleActivationAfterDip` AS `EnableRuleActivationAfterDip`,`Br`.`24HrPriceDipPct` AS `24HrPriceDipPct`,`Br`.`7DPriceDipPct` AS `7DPriceDipPct`,`Br`.`BuyAmountCalculationEnabled` AS `BuyAmountCalculationEnabled`,`Br`.`TotalPurchasesPerRule` AS `TotalPurchasesPerRule`,`Br`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Br`.`RedirectPurchasesToSpreadID` AS `RedirectPurchasesToSpreadID`,`Br`.`PctFromLowBuyPriceEnabled`,`Br`.`NoOfHoursFlatEnabled`,`Br`.`NoOfHoursFlat`,`Br`.`PctOverMinPrice`,`Br`.`DefaultRule`,`Br`.`MultiSellRuleEnabled` ,`Br`.`MultiSellRuleTemplateID`, `Br`.`BuyAmountPctOfTotalEnabled`, `Br`.`BuyAmountPctOfTotal`,`Br`.`EnableRuleActivationAfterDip` as `EnableRuleActivationAfterDipBr`,`Br`.`24HrPriceDipPct` as `24HrPriceDipPctBr`,`Br`.`7DPriceDipPct` as `7DPriceDipPctBr`,`Br`.`RuleType`,`Br`.`SpreadBetRuleID` as SpreadBetRuleIDBr,`Br`.`SpreadBetTotalAmount`,`Br`.`DisableAfterDipPct`
,`Us`.`ID` AS `IDUs`,`Us`.`AccountType` AS `AccountType`,`Us`.`Active` AS `Active`,`Us`.`UserName` AS `UserName`,`Us`.`Password` AS `Password`,`Us`.`ExpiryDate` AS `ExpiryDate`,`Us`.`FirstTimeLogin` AS `FirstTimeLogin`,`Us`.`ResetComplete` AS `ResetComplete`,`Us`.`ResetToken` AS `ResetToken`,`Us`.`Email` AS `Email`,`Us`.`DisableUntil` AS `DisableUntilUs`
,`Uc`.`UserID` AS `UserID2`,`Uc`.`APIKey` AS `APIKey`,`Uc`.`APISecret` AS `APISecret`,`Uc`.`EnableDailyBTCLimit` AS `EnableDailyBTCLimit`,`Uc`.`EnableTotalBTCLimit` AS `EnableTotalBTCLimit`,`Uc`.`DailyBTCLimit` AS `DailyBTCLimit`,`Uc`.`TotalBTCLimit` AS `TotalBTCLimit`,`Uc`.`BTCBuyAmount` AS `BTCBuyAmount`,`Uc`.`CoinSellOffsetEnabled` AS `CoinSellOffsetEnabled2`,`Uc`.`CoinSellOffsetPct` AS `CoinSellOffsetPct2`,`Uc`.`BaseCurrency` AS `BaseCurrency2`,`Uc`.`NoOfCoinPurchase` AS `NoOfCoinPurchase`,`Uc`.`TimetoCancelBuy` AS `TimetoCancelBuy`,`Uc`.`TimeToCancelBuyMins` AS `TimeToCancelBuyMins`,`Uc`.`KEK` AS `KEK`,`Uc`.`MinsToPauseAlert` AS `MinsToPauseAlert`,`Uc`.`LowPricePurchaseEnabled` AS `LowPricePurchaseEnabled`,`Uc`.`NoOfPurchases` AS `NoOfPurchases2`,`Uc`.`PctToPurchase` AS `PctToPurchase`,`Uc`.`TotalRisesInPrice` AS `TotalRisesInPrice`,`Uc`.`TotalRisesInPriceSell` AS `TotalRisesInPriceSell`,`Uc`.`ReservedUSDT` AS `ReservedUSDT`,`Uc`.`ReservedBTC` AS `ReservedBTC`,`Uc`.`ReservedETH` AS `ReservedETH`,`Uc`.`TotalProfitPauseEnabled` AS `TotalProfitPauseEnabled`,`Uc`.`TotalProfitPause` AS `TotalProfitPause`,`Uc`.`PauseRulesEnabled` AS `PauseRulesEnabled`,`Uc`.`PauseRules` AS `PauseRules`,`Uc`.`PauseHours` AS `PauseHours`,`Uc`.`MergeAllCoinsDaily` AS `MergeAllCoinsDaily`,`Uc`.`MarketDropStopEnabled` AS `MarketDropStopEnabled`,`Uc`.`MarketDropStopPct` AS `MarketDropStopPct`,`Uc`.`SellAllCoinsEnabled` AS `SellAllCoinsEnabled`,`Uc`.`SellAllCoinsPct` AS `SellAllCoinsPct`,`Uc`.`CoinModeEmails` AS `CoinModeEmails`,`Uc`.`CoinModeEmailsSell` AS `CoinModeEmailsSell`,`Uc`.`CoinModeMinsToCancelBuy` AS `CoinModeMinsToCancelBuy`,`Uc`.`PctToSave` AS `PctToSave`,`Uc`.`SplitBuyAmounByPctEnabled` AS `SplitBuyAmounByPctEnabled`,`Uc`.`NoOfSplits` AS `NoOfSplits`,`Uc`.`SaveResidualCoins` AS `SaveResidualCoins`,`Uc`.`SpreadBetRuleID` AS `SpreadBetRuleIDUc`,`Uc`.`MinsToPauseAfterPurchase` AS `MinsToPauseAfterPurchase`,`Uc`.`LowMarketModeEnabled` AS `LowMarketModeEnabled`,`Uc`.`LowMarketModeDate` AS `LowMarketModeDate`,`Uc`.`AutoMergeSavings` AS `AutoMergeSavings`,`Uc`.`AllBuyBackAsOverride` AS `AllBuyBackAsOverride`,`Uc`.`TotalPurchasesPerCoin` AS `TotalPurchasesPerCoin`,`Uc`.`PctOfAuto`
,`Cn`.`ID` AS `IDCn`,`Cn`.`Symbol` AS `Symbol`,`Cn`.`Name` AS `NameCn`,ifnull(`Cn`.`BaseCurrency`,`Br`.`LimitToBaseCurrency`) AS `BaseCurrency`,`Cn`.`BuyCoin` AS `BuyCoin2`,`Cn`.`CMCID` AS `CMCID`,`Cn`.`SecondstoUpdate` AS `SecondstoUpdate`,`Cn`.`Image` AS `Image`,`Cn`.`MinTradeSize` AS `MinTradeSize`,`Cn`.`CoinPrecision` AS `CoinPrecision`,`Cn`.`DoNotBuy` AS `DoNotBuy`
,'AutoBuyPrice'
,`Cpmn`.`ID` as `IDCpmn`, `Cpmn`.`Name` as `NameCpmn`, `Cpmn`.`UserID` as `UserIDCpmn`, `Cpmn`.`BuySell`, `Cpmn`.`PriceProjectionEnabled`
,`Cppn`.`ID` as `IDCppn`, `Cppn`.`Name` as `NameCppn`, `Cppn`.`UserID` as `UserIDCppn`, `Cppn`.`BuySell` as `BuySellCppn`
,`C1hPn`.`ID` as `IDC1hPn`, `C1hPn`.`Name` as `NameC1hPn`, `C1hPn`.`UserID` as `UserIDC1hPn`, `C1hPn`.`BuySell` as `BuySellC1hPn`
,(SELECT ((sum(`Cp`.`LiveCoinPrice`-`Cpc`.`Last1HrChange`))/ sum(`Cpc`.`Last1HrChange`))*100 FROM `CoinPrice` `Cp` join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cp`.`CoinID` join `Coin` `Cn` on `Cp`.`CoinID`  = `Cn`.`ID` where `BuyCoin` = 1 ) as `1HrMarketPriceChangeLive`
,(SELECT ((sum(`Cp`.`LiveCoinPrice`-`Cpc`.`Last24HrChange`))/ sum(`Cpc`.`Last24HrChange`))*100 FROM `CoinPrice` `Cp` join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cp`.`CoinID` join `Coin` `Cn` on `Cp`.`CoinID`  = `Cn`.`ID` where `BuyCoin` = 1 ) as `24HrMarketPriceChangeLive`
,(SELECT ((sum(`Cp`.`LiveCoinPrice`-`Cpc`.`Last7DChange`))/ sum(`Cpc`.`Last7DChange`))*100 FROM `CoinPrice` `Cp` join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cp`.`CoinID` join `Coin` `Cn` on `Cp`.`CoinID`  = `Cn`.`ID` where `BuyCoin` = 1 ) as `7DMarketPriceChangeLive`
,`Pds`.`ID` as `IDPds`, `Pds`.`BuyRuleID` as `BuyRuleIDPds`, `Pds`.`PriceDipEnabled` as `PriceDipEnabledPds`, `Pds`.`HoursFlat` as `HoursFlatPds`,`Pds`.`DipStartTime` as `DipStartTimePds`
,`Pdse`.`ID` as `IDPdse`, `Pdse`.`RuleID` as `RuleIDPdse`, `Pdse`.`PriceDipEnable24Hour`, `Pdse`.`PriceDipEnable7Day`, `Pdse`.`HoursFlat`, `Pdse`.`PctTolerance`, `Pdse`.`PriceDipDisable24Hour`, `Pdse`.`PriceDipDisable7Day`
,DateDiff(`Br`.`DisableUntil`,now()) as HoursDisableUntil
,`Mcs`.`LiveBuyOrders` as LiveBuyOrdersMkt, `Mcs`.`LastBuyOrders` as LastBuyOrdersMkt, `Mcs`.`BuyOrdersPctChange` as BuyOrdersPctChangeMkt, `Mcs`.`LiveMarketCap` as LiveMarketCapMkt,`Mcs`.`LastMarketCap` as LastMarketCapMkt, `Mcs`.`MarketCapPctChange` as MarketCapPctChangeMkt, `Mcs`.`Live1HrChange` as Live1HrChangeMkt, `Mcs`.`Last1HrChange` as Last1HrChangeMkt, `Mcs`.`Hr1ChangePctChange` as Hr1ChangePctChangeMkt, `Mcs`.`Live24HrChange` as Live24HrChangeMkt, `Mcs`.`Last24HrChange` as Last24HrChangeMkt, `Mcs`.`Hr24ChangePctChange` as Hr24ChangePctChangeMkt, `Mcs`.`Live7DChange` as Live7DChangeMkt, `Mcs`.`Last7DChange` as Last7DChangeMkt, `Mcs`.`D7ChangePctChange` as D7ChangePctChangeMkt, `Mcs`.`LiveCoinPrice` as LiveCoinPriceMkt, `Mcs`.`LastCoinPrice` as LastCoinPriceMkt, `Mcs`.`CoinPricePctChange` as CoinPricePctChangeMkt, `Mcs`.`LiveSellOrders` as LiveSellOrdersMkt, `Mcs`.`LastSellOrders` as LastSellOrdersMkt
, `Mcs`.`SellOrdersPctChange` as SellOrdersPctChangeMkt, `Mcs`.`LiveVolume` as LiveVolumeMkt, `Mcs`.`LastVolume` as LastVolumeMkt, `Mcs`.`VolumePctChange` as VolumePctChangeMkt, `Mcs`.`Price4Trend` as Price4TrendMkt, `Mcs`.`Price3Trend` as Price3TrendMkt, `Mcs`.`LastPriceTrend` as LastPriceTrendMkt, `Mcs`.`LivePriceTrend` as LivePriceTrendMkt, `Mcs`.`AutoBuyPrice` as AutoBuyPriceMkt, `Mcs`.`1HrPriceChangeLive` as 1HrPriceChangeLiveMkt, `Mcs`.`1HrPriceChangeLast` as 1HrPriceChangeLastMkt, `Mcs`.`1HrPriceChange3` as 1HrPriceChange3Mkt, `Mcs`.`1HrPriceChange4` as 1HrPriceChange4Mkt,`Mcs`.`MaxCoinPricePctChange`, `Mcs`.`MaxHr1ChangePctChange`, `Mcs`.`MaxHr24ChangePctChange`,`Mcs`.`MaxD7ChangePctChange`, `Mcs`.`MinCoinPricePctChange`, `Mcs`.`MinHr1ChangePctChange`, `Mcs`.`MinHr24ChangePctChange`, `Mcs`.`MinD7ChangePctChange`
,`Noot`.`OpenTransactions`, if (`Br`.`DisableUntil`>now(),1,0) as RuleDisabledBr
from (((`BuyRules` `Br`
  join `User` `Us` on((`Us`.`ID` = `Br`.`UserID`)))
  join `UserConfig` `Uc` on((`Uc`.`UserID` = `Us`.`ID`)))
  left join `Coin` `Cn` on((`Cn`.`ID` = `Br`.`LimitToCoinID`)))
  left join `CoinPriceMatchName` `Cpmn` on `Cpmn`.`ID` = `Br`.`CoinPriceMatchID`
  left join `CoinPricePatternName` `Cppn` on `Cppn`.`ID` = `Br`.`CoinPricePatternID`
  left join  `Coin1HrPatternName` `C1hPn` on `C1hPn`.`ID` = `Br`.`Coin1HrPatternID`
  left join `PriceDipStatus` `Pds` on `Pds`.`BuyRuleID` = `Br`.`ID`
  left join `PriceDipSettings` `Pdse` on `Pdse`.`UserID` = `Br`.`UserID`
  join  `MarketCoinStats` `Mcs`
  left join `View25_NoOfOpenTransactions` `Noot` on `Noot`.`BuyRule` = `Br`.`ID` and `Noot`.`UserID` = `Br`.`UserID`
  where `Mcs`.`Hr24ChangePctChange` <> 0 AND `Mcs`.`D7ChangePctChange` <> 0;


  CREATE OR REPLACE VIEW `View14_UserSellRules` as
  SELECT `Sr`.`ID`, `Sr`.`RuleName`, `Sr`.`UserID`, `Sr`.`SellCoin`, `Sr`.`SendEmail`, `Sr`.`BuyOrdersEnabled`, `Sr`.`BuyOrdersTop`, `Sr`.`BuyOrdersBtm`, `Sr`.`MarketCapEnabled`, `Sr`.`MarketCapTop`, `Sr`.`MarketCapBtm`, `Sr`.`1HrChangeEnabled`, `Sr`.`1HrChangeTop`, `Sr`.`1HrChangeBtm`, `Sr`.`24HrChangeEnabled`, `Sr`.`24HrChangeTop`, `Sr`.`24HrChangeBtm`, `Sr`.`7DChangeEnabled`, `Sr`.`7DChangeTop`, `Sr`.`7DChangeBtm`, `Sr`.`ProfitPctEnabled`, `Sr`.`ProfitPctTop`, `Sr`.`ProfitPctBtm`, `Sr`.`CoinPriceEnabled`, `Sr`.`CoinPriceTop`, `Sr`.`CoinPriceBtm`, `Sr`.`SellOrdersEnabled`, `Sr`.`SellOrdersTop`, `Sr`.`SellOrdersBtm`, `Sr`.`VolumeEnabled`, `Sr`.`VolumeTop`, `Sr`.`VolumeBtm`, `Sr`.`CoinOrder`, `Sr`.`SellCoinOffsetEnabled`, `Sr`.`SellCoinOffsetPct`, `Sr`.`SellPriceMinEnabled`, `Sr`.`SellPriceMin`, `Sr`.`LimitToCoin`, `Sr`.`LimitToCoinID`, `Sr`.`AutoSellCoinEnabled`, `Sr`.`AutoSellCoinPct`, `Sr`.`SellPatternEnabled`, `Sr`.`SellPattern`, `Sr`.`LimitToBuyRule`, `Sr`.`CoinPricePatternEnabled`, `Sr`.`CoinPricePattern`, `Sr`.`CoinPriceMatchNameID`, `Sr`.`CoinPricePatternNameID`, `Sr`.`CoinPrice1HrPatternNameID`, `Sr`.`SellFallsInPrice`, `Sr`.`CoinModeRule`, `Sr`.`CoinSwapEnabled`, `Sr`.`CoinSwapAmount`, `Sr`.`NoOfCoinSwapsPerWeek`, `Sr`.`MergeCoinEnabled`,`Sr`.`PctFromHighSellPriceEnabled`,`Sr`.`NoOfHoursFlatEnabled`,`Sr`.`NoOfHoursFlat`,`Sr`.`PctUnderMaxPrice`, `Sr`.`ReEnableBuyRuleEnabled` , `Sr`.`ReEnableBuyRule`,`Sr`.`HoursPastBuyToSellEnabled`, `Sr`.`HoursPastBuyToSell`, `Sr`.`CalculatedSellPctEnabled`, `Sr`.`CalculatedSellPctStart`, `Sr`.`CalculatedSellPctEnd`, `Sr`.`CalculatedSellPctDays`,`Sr`.`BypassTrackingSell`,`Sr`.`CalculatedSellPctReduction`,`Sr`.`OverrideBuyBackAmount`, `Sr`.`OverrideBuyBackSaving`, `Sr`.`HoursAfterPurchaseToStart`, `Sr`.`HoursAfterPurchaseToEnd`, `Sr`.`Category`,`Sr`.`SellRuleType`
 ,`Us`.`ID` AS `IDUs`,`Us`.`AccountType` AS `AccountType`,`Us`.`Active` AS `Active`,`Us`.`UserName` AS `UserName`,`Us`.`Password` AS `Password`,`Us`.`ExpiryDate` AS `ExpiryDate`,`Us`.`FirstTimeLogin` AS `FirstTimeLogin`,`Us`.`ResetComplete` AS `ResetComplete`,`Us`.`ResetToken` AS `ResetToken`,`Us`.`Email` AS `Email`,`Us`.`DisableUntil` AS `DisableUntilUs`
  ,`Uc`.`UserID` AS `UserID2`,`Uc`.`APIKey` AS `APIKey`,`Uc`.`APISecret` AS `APISecret`,`Uc`.`EnableDailyBTCLimit` AS `EnableDailyBTCLimit`,`Uc`.`EnableTotalBTCLimit` AS `EnableTotalBTCLimit`,`Uc`.`DailyBTCLimit` AS `DailyBTCLimit`,`Uc`.`TotalBTCLimit` AS `TotalBTCLimit`,`Uc`.`BTCBuyAmount` AS `BTCBuyAmount`,`Uc`.`CoinSellOffsetEnabled` AS `CoinSellOffsetEnabled2`,`Uc`.`CoinSellOffsetPct` AS `CoinSellOffsetPct2`,`Uc`.`BaseCurrency` AS `BaseCurrency2`,`Uc`.`NoOfCoinPurchase` AS `NoOfCoinPurchase`,`Uc`.`TimetoCancelBuy` AS `TimetoCancelBuy`,`Uc`.`TimeToCancelBuyMins` AS `TimeToCancelBuyMins`,`Uc`.`KEK` AS `KEK`,`Uc`.`MinsToPauseAlert` AS `MinsToPauseAlert`,`Uc`.`LowPricePurchaseEnabled` AS `LowPricePurchaseEnabled`,`Uc`.`NoOfPurchases` AS `NoOfPurchases2`,`Uc`.`PctToPurchase` AS `PctToPurchase`,`Uc`.`TotalRisesInPrice` AS `TotalRisesInPrice`,`Uc`.`TotalRisesInPriceSell` AS `TotalRisesInPriceSell`,`Uc`.`ReservedUSDT` AS `ReservedUSDT`,`Uc`.`ReservedBTC` AS `ReservedBTC`,`Uc`.`ReservedETH` AS `ReservedETH`,`Uc`.`TotalProfitPauseEnabled` AS `TotalProfitPauseEnabled`,`Uc`.`TotalProfitPause` AS `TotalProfitPause`,`Uc`.`PauseRulesEnabled` AS `PauseRulesEnabled`,`Uc`.`PauseRules` AS `PauseRules`,`Uc`.`PauseHours` AS `PauseHours`,`Uc`.`MergeAllCoinsDaily` AS `MergeAllCoinsDaily`,`Uc`.`MarketDropStopEnabled` AS `MarketDropStopEnabled`,`Uc`.`MarketDropStopPct` AS `MarketDropStopPct`,`Uc`.`SellAllCoinsEnabled` AS `SellAllCoinsEnabled`,`Uc`.`SellAllCoinsPct` AS `SellAllCoinsPct`,`Uc`.`CoinModeEmails` AS `CoinModeEmails`,`Uc`.`CoinModeEmailsSell` AS `CoinModeEmailsSell`,`Uc`.`CoinModeMinsToCancelBuy` AS `CoinModeMinsToCancelBuy`,`Uc`.`PctToSave` AS `PctToSave`,`Uc`.`SplitBuyAmounByPctEnabled` AS `SplitBuyAmounByPctEnabled`,`Uc`.`NoOfSplits` AS `NoOfSplits`,`Uc`.`SaveResidualCoins` AS `SaveResidualCoins`,`Uc`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Uc`.`SpreadBetRuleID` AS `SpreadBetRuleIDUc`,`Uc`.`MinsToPauseAfterPurchase` AS `MinsToPauseAfterPurchase`,`Uc`.`LowMarketModeEnabled` AS `LowMarketModeEnabled`,`Uc`.`LowMarketModeDate` AS `LowMarketModeDate`,`Uc`.`AutoMergeSavings` AS `AutoMergeSavings`,`Uc`.`AllBuyBackAsOverride` AS `AllBuyBackAsOverride`,`Uc`.`TotalPurchasesPerCoin` AS `TotalPurchasesPerCoin`,`Uc`.`PctOfAuto`
  ,`Cpmn`.`ID` as `IDCpmn`, `Cpmn`.`Name` as `NameCpmn`, `Cpmn`.`UserID` as `UserIDCpmn`, `Cpmn`.`BuySell`, `Cpmn`.`PriceProjectionEnabled`
  ,`Cppn`.`ID` as `IDCppn`, `Cppn`.`Name` as `NameCppn`, `Cppn`.`UserID` as `UserIDCppn`, `Cppn`.`BuySell` as `BuySellCppn`
  ,`C1hPn`.`ID` as `IDC1hPn`, `C1hPn`.`Name` as `NameC1hPn`, `C1hPn`.`UserID` as `UserIDC1hPn`, `C1hPn`.`BuySell` as `BuySellC1hPn`
  FROM `SellRules` `Sr`
  join `User` `Us` on `Us`.`ID` = `Sr`.`UserID`
  join `UserConfig` `Uc` on `Uc`.`UserID` = `Us`.`ID`
  left join `CoinPriceMatchName` `Cpmn` on `Cpmn`.`ID` = `Sr`.`CoinPriceMatchNameID`
  left join `CoinPricePatternName` `Cppn` on `Cppn`.`ID` = `Sr`.`CoinPricePattern`
  left join  `Coin1HrPatternName` `C1hPn` on `C1hPn`.`ID` = `Sr`.`CoinPrice1HrPatternNameID`;


CREATE OR REPLACE VIEW `View15_OpenTransactions` as
SELECT `Tr`.`ID` AS `IDTr`,`Tr`.`Type` AS `Type`,`Tr`.`CoinID` AS `CoinID`,`Tr`.`UserID` AS `UserID`,`Tr`.`CoinPrice` AS `CoinPrice`,`Tr`.`Amount` AS `Amount`,`Tr`.`Status` AS `StatusTr`,`Tr`.`OrderDate` AS `OrderDate`,`Tr`.`CompletionDate` AS `CompletionDate`,`Tr`.`BittrexID` AS `BittrexID`,`Tr`.`OrderNo` AS `OrderNo`,`Tr`.`BittrexRef` AS `BittrexRefTr`,`Tr`.`BuyOrderCancelTime` AS `BuyOrderCancelTime`,`Tr`.`SellOrderCancelTime` AS `SellOrderCancelTime`,`Tr`.`FixSellRule` AS `FixSellRule`,`Tr`.`BuyRule` AS `BuyRule`,`Tr`.`SellRule` AS `SellRule`,`Tr`.`ToMerge` AS `ToMerge`,`Tr`.`NoOfPurchases` AS `NoOfPurchases`,`Tr`.`NoOfCoinSwapsThisWeek` AS `NoOfCoinSwapsThisWeek`,`Tr`.`NoOfCoinSwapPriceOverrides` AS `NoOfCoinSwapPriceOverrides`,`Tr`.`SpreadBetTransactionID` AS `SpreadBetTransactionID`,`Tr`.`CaptureTrend` AS `CaptureTrend`,`Tr`.`SpreadBetRuleID` AS `SpreadBetRuleID`,`Tr`.`OriginalAmount` AS `OriginalAmount`,`Tr`.`OverrideCoinAllocation` AS `OverrideCoinAllocation`,`Tr`.`DelayCoinSwapUntil` AS `DelayCoinSwapUntil`,`Tr`.`BuyBackTransactionID`
,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name` as `NameCn`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
, `Cn`.`DoNotBuy`
, `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinIDCp`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
,`Ba`.`ID` as `IDBa`, `Ba`.`CoinID`as `CoinID4`, `Ba`.`TransactionID`, `Ba`.`UserID` AS `UserIDBa`, `Ba`.`Type` as `TypeBa`, `Ba`.`BittrexRef` as `BittrexRefBa`, `Ba`.`ActionDate`, `Ba`.`CompletionDate` as `CompletionDateBa`, `Ba`.`Status` as `StatusBa`, `Ba`.`SellPrice`, `Ba`.`RuleID`, `Ba`.`RuleIDSell`, `Ba`.`QuantityFilled`, `Ba`.`MultiplierPrice`, `Ba`.`BuyBack`, `Ba`.`OldBuyBackTransID`, `Ba`.`ResidualAmount`
  From `Transaction` `Tr`
  join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
  join `CoinAskPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
  Left join `BittrexAction` `Ba` on `Ba`.`TransactionID` = `Tr`.`ID` and `Ba`.`Type` = `Tr`.`Type`;


CREATE OR REPLACE VIEW `View16_CoinAllocation` as
SELECT `Us`.`ID` AS `IDUs`,`Us`.`AccountType` AS `AccountType`,`Us`.`Active` AS `Active`,`Us`.`UserName` AS `UserName`,`Us`.`Password` AS `Password`,`Us`.`ExpiryDate` AS `ExpiryDate`,`Us`.`FirstTimeLogin` AS `FirstTimeLogin`,`Us`.`ResetComplete` AS `ResetComplete`,`Us`.`ResetToken` AS `ResetToken`,`Us`.`Email` AS `Email`,`Us`.`DisableUntil` AS `DisableUntilUs`
,`Nca`.`ID`, `Nca`.`UserID`, `Nca`.`USDTAlloc`, `Nca`.`BTCAlloc`, `Nca`.`ETHAlloc`, `Nca`.`PctOnLow`
,`Uc`.`UserID` AS `UserID2`,`Uc`.`APIKey` AS `APIKey`,`Uc`.`APISecret` AS `APISecret`,`Uc`.`EnableDailyBTCLimit` AS `EnableDailyBTCLimit`,`Uc`.`EnableTotalBTCLimit` AS `EnableTotalBTCLimit`,`Uc`.`DailyBTCLimit` AS `DailyBTCLimit`,`Uc`.`TotalBTCLimit` AS `TotalBTCLimit`,`Uc`.`BTCBuyAmount` AS `BTCBuyAmount`,`Uc`.`CoinSellOffsetEnabled` AS `CoinSellOffsetEnabled2`,`Uc`.`CoinSellOffsetPct` AS `CoinSellOffsetPct2`,`Uc`.`BaseCurrency` AS `BaseCurrency2`,`Uc`.`NoOfCoinPurchase` AS `NoOfCoinPurchase`,`Uc`.`TimetoCancelBuy` AS `TimetoCancelBuy`,`Uc`.`TimeToCancelBuyMins` AS `TimeToCancelBuyMins`,`Uc`.`KEK` AS `KEK`,`Uc`.`MinsToPauseAlert` AS `MinsToPauseAlert`,`Uc`.`LowPricePurchaseEnabled` AS `LowPricePurchaseEnabled`,`Uc`.`NoOfPurchases` AS `NoOfPurchases2`,`Uc`.`PctToPurchase` AS `PctToPurchase`,`Uc`.`TotalRisesInPrice` AS `TotalRisesInPrice`,`Uc`.`TotalRisesInPriceSell` AS `TotalRisesInPriceSell`,`Uc`.`ReservedUSDT` AS `ReservedUSDT`,`Uc`.`ReservedBTC` AS `ReservedBTC`,`Uc`.`ReservedETH` AS `ReservedETH`,`Uc`.`TotalProfitPauseEnabled` AS `TotalProfitPauseEnabled`,`Uc`.`TotalProfitPause` AS `TotalProfitPause`,`Uc`.`PauseRulesEnabled` AS `PauseRulesEnabled`,`Uc`.`PauseRules` AS `PauseRules`,`Uc`.`PauseHours` AS `PauseHours`,`Uc`.`MergeAllCoinsDaily` AS `MergeAllCoinsDaily`,`Uc`.`MarketDropStopEnabled` AS `MarketDropStopEnabled`,`Uc`.`MarketDropStopPct` AS `MarketDropStopPct`,`Uc`.`SellAllCoinsEnabled` AS `SellAllCoinsEnabled`,`Uc`.`SellAllCoinsPct` AS `SellAllCoinsPct`,`Uc`.`CoinModeEmails` AS `CoinModeEmails`,`Uc`.`CoinModeEmailsSell` AS `CoinModeEmailsSell`,`Uc`.`CoinModeMinsToCancelBuy` AS `CoinModeMinsToCancelBuy`,`Uc`.`PctToSave` AS `PctToSave`,`Uc`.`SplitBuyAmounByPctEnabled` AS `SplitBuyAmounByPctEnabled`,`Uc`.`NoOfSplits` AS `NoOfSplits`,`Uc`.`SaveResidualCoins` AS `SaveResidualCoins`,`Uc`.`RedirectPurchasesToSpread` AS `RedirectPurchasesToSpread`,`Uc`.`SpreadBetRuleID` AS `SpreadBetRuleIDUc`,`Uc`.`MinsToPauseAfterPurchase` AS `MinsToPauseAfterPurchase`,`Uc`.`LowMarketModeEnabled` AS `LowMarketModeEnabled`,`Uc`.`LowMarketModeDate` AS `LowMarketModeDate`,`Uc`.`AutoMergeSavings` AS `AutoMergeSavings`,`Uc`.`AllBuyBackAsOverride` AS `AllBuyBackAsOverride`,`Uc`.`TotalPurchasesPerCoin` AS `TotalPurchasesPerCoin`
 FROM `User` `Us`
 JOIN `NewCoinAllocations` `Nca` on `Nca`.`UserID` = `Us`.`ID`
 join `UserConfig` `Uc` on `Uc`.`UserID` = `Us`.`ID`;

   CREATE OR REPLACE VIEW `View17_SpreadBetAlerts` as
   SELECT `Ca`.`ID`, `Ca`.`Action`, `Ca`.`Price`, `Ca`.`UserID`,`Ca`.`Status`, `Ca`.`Category`, `Ca`.`ReocurringAlert`, `Ca`.`DateTimeSent`, `Ca`.`SpreadBetAlertRuleID`
   ,`Car`.`ID` as `IDCar`, `Car`.`Name`
   ,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name` as `NameCn`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
   , `Cn`.`DoNotBuy`
   , `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinIDCp`, sum(`Cp`.`LiveCoinPrice`)as `LiveCoinPrice`, sum(`Cp`.`LastCoinPrice`) as `LastCoinPrice`, sum(`Cp`.`Price3`) as `Price3`, sum(`Cp`.`Price4`) as `Price4`, sum(`Cp`.`Price5`) as `Price5`, `Cp`.`LastUpdated`
   ,sum(((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100) as `CoinPricePctChange`
   , `Cmc`.`ID` as `IDCmc`, `Cmc`.`CoinID` as `CoinID2`, sum(`Cmc`.`LiveMarketCap`) as `LiveMarketCap`, sum(`Cmc`.`LastMarketCap`) as `LastMarketCap`, sum(((`Cmc`.`LiveMarketCap`-`Cmc`.`LastMarketCap`)/`Cmc`.`LastMarketCap`)*100) as `MarketCapPctChange`
   , `Cbo`.`ID` as `IDCbo`, `Cbo`.`CoinID` as `CoinID3`, sum(`Cbo`.`LiveBuyOrders`)as `LiveBuyOrders`, sum(`Cbo`.`LastBuyOrders`) as `LastBuyOrders`, sum(((`Cbo`.`LiveBuyOrders`-`Cbo`.`LastBuyOrders`)/`Cbo`.`LastBuyOrders`)* 100) as `BuyOrdersPctChange`
   , `Cv`.`ID` as `IDCv`, `Cv`.`CoinID` as `CoinID4`, sum(`Cv`.`LiveVolume`) as `LiveVolume`, sum(`Cv`.`LastVolume`) as `LastVolume`, sum((( `Cv`.`LiveVolume`- `Cv`.`LastVolume`)/ `Cv`.`LastVolume`)*100) as `VolumePctChange`
   , `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, sum(`Cpc`.`Live1HrChange`) as `Live1HrChange`, sum(`Cpc`.`Last1HrChange`) as `Last1HrChange`, sum(`Cpc`.`Live24HrChange`) as `Live24HrChange`, sum(`Cpc`.`Last24HrChange`) as `Last24HrChange`, sum(`Cpc`.`Live7DChange`) as `Live7DChange`, sum(`Cpc`.`Last7DChange`) as `Last7DChange`, sum(`Cpc`.`1HrChange3`) as `1HrChange3`
   , sum(`Cpc`.`1HrChange4`) as `1HrChange4`, sum(`Cpc`.`1HrChange5`) as `1HrChange5`, sum(((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100)  as `Hr1ChangePctChange`, sum((( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100) as `Hr24ChangePctChange`
   , sum(((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100) as `D7ChangePctChange`
   ,`Cso`.`ID`as `IDCso`, `Cso`.`CoinID` as `CoinIDCso`, sum(`Cso`.`LiveSellOrders`) as `LiveSellOrders`, sum(`Cso`.`LastSellOrders`) as `LastSellOrders`,sum(((`Cso`.`LiveSellOrders`-`Cso`.`LastSellOrders`)/`Cso`.`LastSellOrders`)*100) as `SellOrdersPctChange`
   ,if(sum(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice`) > 0, 1, if(sum(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice`) < 0, -1, 0)) as  `LivePriceTrend`
             ,if(sum(`Cp`.`LastCoinPrice` -`Cp`.`Price3`) > 0, 1, if(sum(`Cp`.`LastCoinPrice` -`Cp`.`Price3`) < 0, -1, 0)) as  `LastPriceTrend`
             ,if(sum(`Cp`.`Price3` -`Cp`.`Price4`) > 0, 1, if(sum(`Cp`.`Price3` -`Cp`.`Price4`) < 0, -1, 0)) as  `Price3Trend`
             ,if(sum(`Cp`.`Price4` -`Cp`.`Price5`) > 0, 1, if(sum(`Cp`.`Price4` -`Cp`.`Price5`) < 0, -1, 0)) as  `Price4Trend`
   ,if(sum(`Cpc`.`Live1HrChange`-`Last1HrChange`) >0,1,if(sum(`Cpc`.`Live1HrChange`-`Last1HrChange`) <0,-1,0)) as `1HrPriceChangeLive`
   ,if(sum(`Last1HrChange`-`1HrChange3`)>0,1,if(sum(`Last1HrChange`-`1HrChange3`)<0,-1,0)) as `1HrPriceChangeLast`
   ,if(sum(`1HrChange3`-`1HrChange4`)>0,1,if(sum(`1HrChange3`-`1HrChange4`)<0,-1,0)) as `1HrPriceChange3`
   ,if(sum(`1HrChange4`-`1HrChange5`)>0,1,if(sum(`1HrChange4`-`1HrChange5`)<0,-1,0)) as `1HrPriceChange4`
    ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
    , `Us`.`DisableUntil`
    ,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
    , `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
    , `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
    , `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
    , `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
    , `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
    , `Uc`.`TotalPurchasesPerCoin`
   FROM `SpreadBetAlerts` `Ca`
   join `SpreadBetAlertsRule`  `Car` on `Car`.`ID` = `Ca`.`SpreadBetAlertRuleID`
   join `SpreadBetCoins` `Sbc` on `Sbc`.`CoinID` = `Ca`.`ID`
   join `SpreadBetTransactions` `Sbt` on `Sbt`.`SpreadBetRuleID` = `Sbc`.`SpreadBetRuleID`
   join `Coin` `Cn` on `Cn`.`ID` = `Sbc`.`CoinID`
   join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Sbc`.`CoinID`
   join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Sbc`.`CoinID`
   join `CoinBuyOrders` `Cbo` on `Cbo`.`CoinID` = `Sbc`.`CoinID`
   join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Sbc`.`CoinID`
   join `CoinSellOrders` `Cso` on `Cso`.`CoinID` = `Sbc`.`CoinID`
   join `CoinVolume` `Cv` on `Cv`.`CoinID` = `Sbc`.`CoinID`
   join `User` `Us` on `Us`.`ID` = `Ca`.`UserID`
   join `UserConfig` `Uc` on `Uc`.`UserID` = `Ca`.`UserID`
   where `Cn`.`BuyCoin` = 1 and `Cn`.`DoNotBuy` = 0
   group by `Sbt`.`ID`;

   CREATE OR REPLACE VIEW `View18_CoinMode` as
   SELECT `Cmr`.`ID`, `Cmr`.`CoinID`, `Cmr`.`RuleID`, `Cmr`.`ModeID`, `Cmr`.`RuleIDSell`, `Cmr`.`USDBuyAmount`, `Cmr`.`Hr1Top`, `Cmr`.`Hr1Btm`, `Cmr`.`Hr24Top`, `Cmr`.`Hr24Btm`, `Cmr`.`D7Top`, `Cmr`.`D7Btm`, `Cmr`.`SecondarySellRules`, `Cmr`.`UserID`, `Cmr`.`CoinModeBuyRuleEnabled`, `Cmr`.`CoinModeSellRuleEnabled`, `Cmr`.`PctToBuy`, `Cmr`.`CountToActivateBuyMode`, `Cmr`.`BuyModeCount`, `Cmr`.`NoOfTransactions`, `Cmr`.`LowestPctProfit`, `Cmr`.`AvgTimeToSell`, `Cmr`.`NextReviewDate`
   ,`Ath`. `ID` as `IDAth`, `Ath`. `CoinID` as `CoinIDAth`, `Ath`. `HighLow` as `HighAth`, `Ath`. `Price` as `PriceAth`
   ,`Atl`. `ID` as `IDAtl`, `Atl`. `CoinID` as `CoinIDAtl`, `Atl`. `HighLow` as `HighAtl`, `Atl`. `Price` as `PriceAtl`
   ,`M6p`.`ID` as `IDM6p`, `M6p`.`CoinID` as `CoinIDM6p`, `M6p`.`MaxPrice` as `SixMonthHighPrice`, `M6p`.`Month`, `M6p`.`Year`
   ,`M6pm`.`ID` as `IDM6pm`, `M6pm`.`CoinID` as `CoinIDM6pm`, `M6pm`.`MinPrice` as `SixMonthMinPrice`, `M6pm`.`Month` as `MonthM6pm`, `M6pm`.`Year` as `YearM6pm`
   , `Cp`.`ID` as `IDCp`, `Cp`.`CoinID` as `CoinIDCp`, `Cp`.`LiveCoinPrice` as `LiveCoinPrice`, `Cp`.`LastCoinPrice` as `LastCoinPrice`, `Cp`.`Price3` as `Price3`, `Cp`.`Price4` as `Price4`, `Cp`.`Price5` as `Price5`, `Cp`.`LastUpdated`
   ,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
   , `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange` as `Live1HrChange`, `Cpc`.`Last1HrChange` as `Last1HrChange`, `Cpc`.`Live24HrChange` as `Live24HrChange`, `Cpc`.`Last24HrChange` as `Last24HrChange`, `Cpc`.`Live7DChange` as `Live7DChange`, `Cpc`.`Last7DChange` as `Last7DChange`, `Cpc`.`1HrChange3` as `1HrChange3`
   , `Cpc`.`1HrChange4` as `1HrChange4`, `Cpc`.`1HrChange5` as `1HrChange5`, ((`Cpc`.`Live1HrChange`-`Cpc`.`Last1HrChange`)/`Cpc`.`Last1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cpc`.`Last24HrChange`- `Cpc`.`Last24HrChange`)/ `Cpc`.`Last24HrChange`)*100 as `Hr24ChangePctChange`
   , ((`Cpc`.`Live7DChange`-`Cpc`.`Last7DChange`)/`Cpc`.`Last7DChange`)*100 as `D7ChangePctChange`
   , `Pppm`.`ID` as `IDPppm`, `Pppm`.`CoinID` as `CoinIDPppm`, `Pppm`.`0Min` as `0MinPppm`, `Pppm`.`15Min` as `15MinPppm`, `Pppm`.`30Min` as `30MinPppm`, `Pppm`.`45Min` as `45MinPppm`, `Pppm`.`60Min` as `60MinPppm`, `Pppm`.`75Min` as `75MinPppm`
   , `Pppi`.`ID` as `IDPppi`, `Pppi`.`CoinID` as `CoinIDPppi`, `Pppi`.`0Min` as `0MinPppi`, `Pppi`.`15Min` as `15MinPppi`, `Pppi`.`30Min` as `30MinPppi`, `Pppi`.`45Min` as `45MinPppi`, `Pppi`.`60Min` as `60MinPppi`, `Pppi`.`75Min` as `75MinPppi`
   , `Bbs`.`ID` as `IDBbs`, `Bbs`.`CoinID` as `CoinIDBbs`, `Bbs`.`LastPriceChange`, `Bbs`.`Min15PriceChange`, `Bbs`.`Min30PriceChange`, `Bbs`.`Min45PriceChange`, `Bbs`.`Min75PriceChange`, `Bbs`.`OneHrPriceChange`, `Bbs`.`Twenty4HrPriceChange`, `Bbs`.`MarketPriceChange`, `Bbs`.`Days7PriceChange`
   ,`Uc`.`UserID` as `UserID2`,`Uc`.`APIKey`,`Uc`.`APISecret`,`Uc`.`EnableDailyBTCLimit`, `Uc`.`EnableTotalBTCLimit`, `Uc`.`DailyBTCLimit`, `Uc`.`TotalBTCLimit`, `Uc`.`BTCBuyAmount`, `Uc`.`CoinSellOffsetEnabled` as `CoinSellOffsetEnabled2`
   , `Uc`.`CoinSellOffsetPct` as `CoinSellOffsetPct2`, `Uc`.`BaseCurrency` as `BaseCurrency2`, `Uc`.`NoOfCoinPurchase`, `Uc`.`TimetoCancelBuy`, `Uc`.`TimeToCancelBuyMins`, `Uc`.`KEK`, `Uc`.`MinsToPauseAlert`, `Uc`.`LowPricePurchaseEnabled`
   , `Uc`.`NoOfPurchases` as `NoOfPurchases2`, `Uc`.`PctToPurchase`, `Uc`.`TotalRisesInPrice`, `Uc`.`TotalRisesInPriceSell`, `Uc`.`ReservedUSDT`, `Uc`.`ReservedBTC`, `Uc`.`ReservedETH`, `Uc`.`TotalProfitPauseEnabled`
   , `Uc`.`TotalProfitPause`, `Uc`.`PauseRulesEnabled`, `Uc`.`PauseRules`, `Uc`.`PauseHours`, `Uc`.`MergeAllCoinsDaily`, `Uc`.`MarketDropStopEnabled`, `Uc`.`MarketDropStopPct`, `Uc`.`SellAllCoinsEnabled`
   , `Uc`.`SellAllCoinsPct`, `Uc`.`CoinModeEmails`, `Uc`.`CoinModeEmailsSell`, `Uc`.`CoinModeMinsToCancelBuy`, `Uc`.`PctToSave`, `Uc`.`SplitBuyAmounByPctEnabled`, `Uc`.`NoOfSplits`, `Uc`.`SaveResidualCoins`
   , `Uc`.`RedirectPurchasesToSpread`, `Uc`.`SpreadBetRuleID` as `SpreadBetRuleIDUc`, `Uc`.`MinsToPauseAfterPurchase`, `Uc`.`LowMarketModeEnabled`, `Uc`.`LowMarketModeDate`, `Uc`.`AutoMergeSavings`, `Uc`.`AllBuyBackAsOverride`
   , `Uc`.`TotalPurchasesPerCoin`
   ,`Us`.`ID` as `IDUs`, `Us`.`AccountType`, `Us`.`Active`, `Us`.`UserName`, `Us`.`Password`, `Us`.`ExpiryDate`, `Us`.`FirstTimeLogin`, `Us`.`ResetComplete`, `Us`.`ResetToken`, `Us`.`Email`
   , `Us`.`DisableUntil`
   ,`Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name` as `NameCn`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin` as `BuyCoin2`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
   , `Cn`.`DoNotBuy`
   FROM `CoinModeRules` `Cmr`
   left join `MonthlyMaxPrices` `M6p` on `Cmr`.`CoinID` =`M6p`.`CoinID` and `M6p`.`Month` = Month(DATE_SUB(now(), INTERVAL 6 MONTH)) and `M6p`.`Year` = Year(DATE_SUB(now(), INTERVAL 6 MONTH))
   left join `MonthlyMinPrices` `M6pm` on `Cmr`.`CoinID` =`M6pm`.`CoinID` and `M6pm`.`Month` = Month(DATE_SUB(now(), INTERVAL 6 MONTH)) and `M6pm`.`Year` = Year(DATE_SUB(now(), INTERVAL 6 MONTH))
   left join `AllTimeHighLow` `Ath` on `Ath`.`CoinID` = `Cmr`.`CoinID` and `Ath`.`HighLow` = 'High'
   left join `AllTimeHighLow` `Atl` on `Atl`.`CoinID` = `Cmr`.`CoinID` and `Atl`.`HighLow` = 'Low'
   join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Cmr`.`CoinID`
   join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cmr`.`CoinID`
   left join `ProjectedPriceMax` `Pppm` on `Pppm`.`CoinID` = `Cmr`.`CoinID`
   left join `ProjectedPriceMin` `Pppi` on `Pppi`.`CoinID` = `Cmr`.`CoinID`
   left Join `BearBullStats` `Bbs` on `Bbs`.`CoinID` = `Cmr`.`CoinID`
   join `UserConfig` `Uc` on `Uc`.`UserID` = `Cmr`.`UserID`
   join `User` `Us` on `Us`.`ID` = `Cmr`.`UserID`
   join `Coin` `Cn` on `Cn`.`ID` = `Cmr`.`CoinID`;

   CREATE OR REPLACE VIEW `View19_MaxHighLow` as
   SELECT `Ath`.`CoinID`,max(`Ath`.`Price`) as `HighPrice`, Min(`Atl`.`Price`) as LowPrice, `Ah`.`3MonthPrice` as Month3High, `Ah`.`6MonthPrice` as Month6High, `Al`.`3MonthPrice` as Month3Low, `Al`.`6MonthPrice` as Month6Low,`Cp`.`LiveCoinPrice`,`Ah`.`DateAdded`
   from `AllTimeHighLow` `Ath`
   join `AllTimeHighLow` `Atl` on `Atl`.`CoinID` =`Ath`.`CoinID`
   join `Coin` `Cn` on `Cn`.`ID` = `Ath`.`CoinID`
   join `CoinBidPrice` `Cp` on `Cp`.`CoinID` = `Ath`.`CoinID`
   Join `AvgHighLow` `Ah` on `Ah`.`CoinID` = `Ath`.`CoinID` and `Ah`.`HighLow` = 'High'
   Join `AvgHighLow` `Al` on `Al`.`CoinID` = `Ath`.`CoinID` and `Al`.`HighLow` = 'Low'
   where `Ath`.`HighLow` = 'High' and `Cn`.`BuyCoin` = 1
   group by `Ath`.`CoinID`;

   CREATE OR REPLACE VIEW `View20_CoinPrices` as
   SELECT `Cn`.`ID` as `IDCn`, `Cn`.`Symbol`, `Cn`.`Name`, `Cn`.`BaseCurrency`, `Cn`.`BuyCoin`, `Cn`.`CMCID`, `Cn`.`SecondstoUpdate`, `Cn`.`Image`, `Cn`.`MinTradeSize`, `Cn`.`CoinPrecision`
    , `Cn`.`DoNotBuy`
    , `Cp`.`ID` as `IDCp`, `Cp`.`CoinID`, `Cp`.`LiveCoinPrice`, `Cp`.`LastCoinPrice`, `Cp`.`Price3`, `Cp`.`Price4`, `Cp`.`Price5`, `Cp`.`LastUpdated`
    ,((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100 as `CoinPricePctChange`
    , `Cmc`.`ID` as `IDCmc`, `Cmc`.`CoinID` as `CoinID2`, `Cmc`.`LiveMarketCap`, `Cmc`.`LastMarketCap`, ((`Cmc`.`LiveMarketCap`-`Cmc`.`LastMarketCap`)/`Cmc`.`LastMarketCap`)*100 as `MarketCapPctChange`
    , `Cbo`.`ID` as `IDCbo`, `Cbo`.`CoinID` as `CoinID3`, `Cbo`.`LiveBuyOrders`, `Cbo`.`LastBuyOrders`, ((`Cbo`.`LiveBuyOrders`-`Cbo`.`LastBuyOrders`)/`Cbo`.`LastBuyOrders`)* 100 as `BuyOrdersPctChange`
    , `Cv`.`ID` as `IDCv`, `Cv`.`CoinID` as `CoinID4`, `Cv`.`LiveVolume`, `Cv`.`LastVolume`, (( `Cv`.`LiveVolume`- `Cv`.`LastVolume`)/ `Cv`.`LastVolume`)*100 as `VolumePctChange`
    , `Cpc`.`ID` as `IDCpc`, `Cpc`.`CoinID` as `CoinID5`, `Cpc`.`Live1HrChange`, `Cpc`.`Last1HrChange`, `Cpc`.`Live24HrChange`, `Cpc`.`Last24HrChange`, `Cpc`.`Live7DChange`, `Cpc`.`Last7DChange`, `Cpc`.`1HrChange3`
    , `Cpc`.`1HrChange4`, `Cpc`.`1HrChange5`, ((`Cp`.`LiveCoinPrice`-`Cpc`.`Live1HrChange`)/`Cpc`.`Live1HrChange`)*100  as `Hr1ChangePctChange`, (( `Cp`.`LiveCoinPrice`- `Cpc`.`Live24HrChange`)/ `Cpc`.`Live24HrChange`)*100 as `Hr24ChangePctChange`
    , ((`Cp`.`LiveCoinPrice`-`Cpc`.`Live7DChange`)/`Cpc`.`Live7DChange`)*100 as `D7ChangePctChange`
    ,`Cso`.`ID`as `IDCso`, `Cso`.`CoinID` as `CoinIDCso`, `Cso`.`LiveSellOrders`, `Cso`.`LastSellOrders`,((`Cso`.`LiveSellOrders`-`Cso`.`LastSellOrders`)/`Cso`.`LastSellOrders`)*100 as `SellOrdersPctChange`
    ,if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` > 0, 1, if(`Cp`.`LiveCoinPrice` -`Cp`.`LastCoinPrice` < 0, -1, 0)) as  `LivePriceTrend`
             ,if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` > 0, 1, if(`Cp`.`LastCoinPrice` -`Cp`.`Price3` < 0, -1, 0)) as  `LastPriceTrend`
             ,if(`Cp`.`Price3` -`Cp`.`Price4` > 0, 1, if(`Cp`.`Price3` -`Cp`.`Price4` < 0, -1, 0)) as  `Price3Trend`
             ,if(`Cp`.`Price4` -`Cp`.`Price5` > 0, 1, if(`Cp`.`Price4` -`Cp`.`Price5` < 0, -1, 0)) as  `Price4Trend`
    ,if(`Cpc`.`Live1HrChange`-`Last1HrChange` >0,1,if(`Cpc`.`Live1HrChange`-`Last1HrChange` <0,-1,0)) as `1HrPriceChangeLive`
    ,if(`Last1HrChange`-`1HrChange3`>0,1,if(`Last1HrChange`-`1HrChange3`<0,-1,0)) as `1HrPriceChangeLast`
    ,if(`1HrChange3`-`1HrChange4`>0,1,if(`1HrChange3`-`1HrChange4`<0,-1,0)) as `1HrPriceChange3`
    ,if(`1HrChange4`-`1HrChange5`>0,1,if(`1HrChange4`-`1HrChange5`<0,-1,0)) as `1HrPriceChange4`
    ,`Pdcs`.`ID` as `IDPdcs`, `Pdcs`.`CoinID` as `CoinIDPdcs`, `Pdcs`.`PriceDipEnabled` as `PriceDipEnabledPdcs`, `Pdcs`.`HoursFlat` as `HoursFlatPdcs`, `Pdcs`.`DipStartTime` as `DipStartTimePdcs`, `Pdcs`.`HoursFlatLow` as `HoursFlatLowPdcs`, `Pdcs`.`HoursFlatHigh` as `HoursFlatHighPdcs`,`Pdcs`.`MaxHoursFlat`
    ,avgMinPrice(`Cn`.`ID`,20) as `MinPriceFromLow`, ((`Cp`.`LiveCoinPrice`- avgMinPrice(`Cn`.`ID`,20))/avgMinPrice(`Cn`.`ID`,20))*100 as `PctFromLiveToLow`
    ,'ID' as `IDAhl`, 'HighLow', `v19Athl`.`Month3High`, `v19Athl`.`Month6High`, `v19Athl`.`CoinID` as `CoinIDv19Athl`, 'LastUpdated' as `LastUpdatedAhl` ,(`v19Athl`.`Month6Low`+`v19Athl`.`Month3Low`)/2 as AverageLowPrice, TIMESTAMPDIFF(HOUR, `v19Athl`.`DateAdded`, now()) as HoursSinceAdded, `v19Athl`.`Month3Low`, `v19Athl`.`Month6Low`
    FROM `Coin` `Cn`
    join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Cn`.`ID`
    join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Cn`.`ID`
    join `CoinBuyOrders` `Cbo` on `Cbo`.`CoinID` = `Cn`.`ID`
    join `CoinVolume` `Cv` on `Cv`.`CoinID` = `Cn`.`ID`
    join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cn`.`ID`
    join `CoinSellOrders` `Cso` on `Cso`.`CoinID` = `Cn`.`ID`
    Left join `PriceDipCoinStatus` `Pdcs` on `Pdcs`.`CoinID` =  `Cn`.`ID`
    left Join `View19_MaxHighLow` `v19Athl` on `v19Athl`.`CoinID` = `Cn`.`ID`
     where `Cn`.`BuyCoin` = 1;

  CREATE OR REPLACE VIEW `View21_MarketStats` as
     SELECT sum(`Cp`.`LiveCoinPrice`) as LiveCoinPrice, sum(`Cp`.`LastCoinPrice`) as LastCoinPrice, sum(`Cp`.`Price3`) as Price3, sum(`Cp`.`Price4`) as Price4, sum(`Cp`.`Price5`) as Price5
     ,sum(((`Cp`.`LiveCoinPrice`-`Cp`.`LastCoinPrice`)/`Cp`.`LastCoinPrice`)*100) as `CoinPricePctChange`
     , sum(`Cmc`.`LiveMarketCap`) as LiveMarketCap, sum(`Cmc`.`LastMarketCap`) as LastMarketCap, sum(((`Cmc`.`LiveMarketCap`-`Cmc`.`LastMarketCap`)/`Cmc`.`LastMarketCap`)*100) as `MarketCapPctChange`
     ,  sum(`Cbo`.`LiveBuyOrders`) as LiveBuyOrders, sum(`Cbo`.`LastBuyOrders`) as LastBuyOrders, sum(((`Cbo`.`LiveBuyOrders`-`Cbo`.`LastBuyOrders`)/`Cbo`.`LastBuyOrders`)* 100) as `BuyOrdersPctChange`
     , sum(`Cv`.`LiveVolume`) as LiveVolume, sum(`Cv`.`LastVolume`) as LastVolume, sum((( `Cv`.`LiveVolume`- `Cv`.`LastVolume`)/ `Cv`.`LastVolume`)*100) as `VolumePctChange`
     , sum(`Cpc`.`Live1HrChange`) as Live1HrChange, sum(`Cpc`.`Last1HrChange`) as Last1HrChange, sum(`Cpc`.`Live24HrChange`) as Live24HrChange, sum(`Cpc`.`Last24HrChange`) as Last24HrChange, sum(`Cpc`.`Live7DChange`) as Live7DChange, sum(`Cpc`.`Last7DChange`) as Last7DChange, sum(`Cpc`.`1HrChange3`) as 1HrChange3
     , sum(`Cpc`.`1HrChange4`) as 1HrChange4, sum(`Cpc`.`1HrChange5`) as 1HrChange5
     , ((sum(`Cp`.`LiveCoinPrice`) - sum(`Cpc`.`Live1HrChange`))/sum(`Cp`.`LiveCoinPrice`))*100  as `Hr1ChangePctChange`
     , (( sum(`Cp`.`LiveCoinPrice`)- sum(`Cpc`.`Live24HrChange`))/sum(`Cp`.`LiveCoinPrice`))*100 as `Hr24ChangePctChange`
     , ((sum(`Cp`.`LiveCoinPrice`) - sum(`Cpc`.`Live7DChange`))/sum(`Cp`.`LiveCoinPrice`))*100 as `D7ChangePctChange`
     , sum(`Cso`.`LiveSellOrders`) as LiveSellOrders,sum(`Cso`.`LastSellOrders`) as LastSellOrders,sum(((`Cso`.`LiveSellOrders`-`Cso`.`LastSellOrders`)/`Cso`.`LastSellOrders`)*100) as `SellOrdersPctChange`
     ,if(sum(`Cp`.`LiveCoinPrice`) -sum(`Cp`.`LastCoinPrice`) > 0, 1, if(sum(`Cp`.`LiveCoinPrice`) -sum(`Cp`.`LastCoinPrice`) < 0, -1, 0)) as  `LivePriceTrend`
               ,if(sum(`Cp`.`LastCoinPrice`) -sum(`Cp`.`Price3`) > 0, 1, if(sum(`Cp`.`LastCoinPrice`) -sum(`Cp`.`Price3`) < 0, -1, 0)) as  `LastPriceTrend`
               ,if(sum(`Cp`.`Price3`) -sum(`Cp`.`Price4`) > 0, 1, if(sum(`Cp`.`Price3`) -sum(`Cp`.`Price4`) < 0, -1, 0)) as  `Price3Trend`
               ,if(sum(`Cp`.`Price4`) -sum(`Cp`.`Price5`) > 0, 1, if(sum(`Cp`.`Price4`) -sum(`Cp`.`Price5`) < 0, -1, 0)) as  `Price4Trend`
     ,if(sum(`Cpc`.`Live1HrChange`)-sum(`Last1HrChange`) >0,1,if(sum(`Cpc`.`Live1HrChange`)-sum(`Last1HrChange`) <0,-1,0)) as `1HrPriceChangeLive`
     ,if(sum(`Last1HrChange`)-sum(`1HrChange3`)>0,1,if(sum(`Last1HrChange`)-sum(`1HrChange3`)<0,-1,0)) as `1HrPriceChangeLast`
     ,if(sum(`1HrChange3`)-sum(`1HrChange4`)>0,1,if(sum(`1HrChange3`)-sum(`1HrChange4`)<0,-1,0)) as `1HrPriceChange3`
     ,if(sum(`1HrChange4`)-sum(`1HrChange5`)>0,1,if(sum(`1HrChange4`)-sum(`1HrChange5`)<0,-1,0)) as `1HrPriceChange4`
     FROM `Coin` `Cn`
     join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Cn`.`ID`
     join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Cn`.`ID`
     join `CoinBuyOrders` `Cbo` on `Cbo`.`CoinID` = `Cn`.`ID`
     join `CoinVolume` `Cv` on `Cv`.`CoinID` = `Cn`.`ID`
     join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cn`.`ID`
     join `CoinSellOrders` `Cso` on `Cso`.`CoinID` = `Cn`.`ID`
       where `Cn`.`BuyCoin` = 1 and `Cn`.`DoNotBuy` = 0;

CREATE OR REPLACE VIEW `View22_BuyBackTransationIDProfit` as
       SELECT  `BuyBackTransactionID`,  if(`BaseCurrency` = 'BTC',sum((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))/100)*0.28)* getBTCPrice(84)) ,if(`BaseCurrency` = 'ETH'
                ,sum((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))/100)*0.28)* getBTCPrice(85)) ,if(`BaseCurrency` = 'USDT'
                  ,sum((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))/100)*0.28)) ,0)))as USDProfit
                  , `BaseCurrency`,`UserID`
              FROM `View15_OpenTransactions`
              WHERE `UserID` = 3 and `Type` = 'Sell' and `StatusTr` = 'Sold' and `BuyBackTransactionID` <> 0
              Group by `BuyBackTransactionID`, `BaseCurrency` order by `USDProfit` asc;

CREATE OR REPLACE VIEW `View23_AvgCoinPricePct` as
  SELECT `Cn`.`Symbol`,`Cp`.`CoinID`, `Cp`.`LiveCoinPrice`, avgMaxPrice(`Cp`.`CoinID`,20) as avgMaxPrice, avgMinPrice(`Cp`.`CoinID`,20) as AvgMinPrice
  ,((`Cp`.`LiveCoinPrice` - avgMaxPrice(`Cp`.`CoinID`,20))/`Cp`.`LiveCoinPrice`)*100 as DiffFromMax
  ,((`Cp`.`LiveCoinPrice` - avgMinPrice(`Cp`.`CoinID`,20))/`Cp`.`LiveCoinPrice`)*100 as DiffFromMin
  ,`Cn`.`DoNotBuy`,`Cn`.`BaseCurrency`
  FROM `CoinPrice` `Cp`
  join `Coin` `Cn` on `Cn`.`ID` = `Cp`.`CoinID`
  where `Cn`.`BuyCoin` = 1
  order by DiffFromMin asc;

CREATE OR REPLACE VIEW `View24_SavingsReadyToOpenAndMerge` as
SELECT `TrSav`.`ID` as SavingID, `Tr`.`ID`,`Tr`.`UserID`,`Uc`.`MergeSavingWithPurchase`,`Tr`.`FixSellRule`, `Tr`.`BuyRule`, `Tr`.`SellRule`,`Tr`.`MultiSellRuleEnabled`, `Tr`.`MultiSellRuleTemplateID`
FROM `Transaction` `Tr`
join `Transaction` `TrSav` on `Tr`.`CoinID` = `TrSav`.`CoinID`
join `UserConfig` `Uc` on `Uc`.`UserID` = `Tr`.`UserID`
Where `Tr`.`Status` = 'Open' and `TrSav`.`Status` = 'Saving' and `Uc`.`MergeSavingWithPurchase` = 1;

CREATE OR REPLACE VIEW `View25_NoOfOpenTransactions` AS
SELECT Count(`ID`) as OpenTransactions ,`BuyRule`,`UserID`
FROM `Transaction`
WHERE `Status` in ('Pending','Open')
group by `BuyRule`,`UserID`;
