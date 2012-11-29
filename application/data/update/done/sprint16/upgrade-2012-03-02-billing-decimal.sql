alter table billing 
    modify column amount DECIMAL(20,6),
    modify column prov DECIMAL(20,6),
    modify column voucher DECIMAL(20,6),
    modify column item1Value DECIMAL(20,6),
    modify column item2Value DECIMAL(20,6),
    modify column tax1Value DECIMAL(20,6),
    modify column tax2Value DECIMAL(20,6),
    modify column item1Key DECIMAL(8,2),
    modify column item2Key DECIMAL(8,2);

alter table billing_assets
    modify column mwst DECIMAL(20,6),
    modify column courierMwst DECIMAL(20,6);
    