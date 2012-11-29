alter table `orders` add hashtag char(32) not null;
alter table `orders` add index(hashtag);
update orders set hashtag=MD5(CONCAT('hKtER55xpuemj', `id`, 'hKtER55xpuemj'));