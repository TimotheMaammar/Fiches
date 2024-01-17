# PROJET NET201 - Étape 1

## Routeur fédérateur

    enable
    config
    interface GigabitEthernet1/0
    ip addr 10.0.10.5 255.255.255.0
    no shut
    
    interface GigabitEthernet2/0
    ip addr 10.0.20.5 255.255.255.0
    no shut
    
    interface GigabitEthernet3/0
    ip addr 10.0.30.5 255.255.255.0
    no shut
    
    interface GigabitEthernet4/0
    ip addr 10.0.40.5 255.255.255.0
    no shut
    
    interface GigabitEthernet5/0
    ip addr 10.0.50.5 255.255.255.0
    no shut
    
    interface GigabitEthernet6/0
    ip addr 10.0.60.5 255.255.255.0
    no shut
    
    interface GigabitEthernet7/0
    ip addr 10.0.70.5 255.255.255.0
    no shut
    
    interface GigabitEthernet8/0
    ip addr 10.0.80.5 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.10.0
    network 10.0.20.0
    network 10.0.30.0
    network 10.0.40.0
    network 10.0.50.0
    network 10.0.60.0
    network 10.0.70.0
    network 10.0.80.0
    debug ip rip

## Internet 

### Routeur INTERNET-PARIS

    enable
    config
    interface GigabitEthernet0/0/0
    ip address 192.168.10.100 255.255.255.0
    interface Serial0/1/0
    ip address 100.100.100.10 255.255.255.0
    ip route 192.168.0.0 255.255.0.0 192.168.10.1
    ip route 10.0.0.0 255.0.0.0 192.168.10.1
    ip route 0.0.0.0 0.0.0.0 100.100.100.11


### Routeur INTERNET-BERLIN

    enable
    config
    interface GigabitEthernet0/0/0
    ip address 192.168.60.100 255.255.255.0
    interface Serial0/1/0
    ip address 101.100.100.60 255.255.255.0
    ip route 0.0.0.0 0.0.0.0 101.100.100.61

### Routeur INTERNET

    enable
    config
    interface Serial0/1/0
    ip address 100.100.100.11 255.255.255.0
    interface Serial0/1/1
    ip address 101.100.100.61 255.255.255.0
    ip route 10.0.0.0 255.0.0.0 100.100.100.10
    ip route 192.168.0.0 255.255.0.0 100.100.100.10

## France

### Routeur PARIS

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.10.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.10.2 255.255.255.0
    no shut

    ip route 0.0.0.0 0.0.0.0 192.168.10.100
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.10.0
    network 192.168.10.0
    redistribute static

### Routeur LYON

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.20.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.20.2 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.20.0
    network 192.168.20.0

### Routeur TOULOUSE

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.30.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.30.2 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.30.0
    network 192.168.30.0

## Allemagne

### Routeur MUNICH

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.40.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.40.2 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.40.0
    network 192.168.40.0

### Routeur FRANKFURT

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.50.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.50.2 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.50.0
    network 192.168.50.0

### Routeur BERLIN

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.60.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.60.2 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.60.0
    network 192.168.60.0

## Espagne

### Routeur MADRID

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.70.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.70.2 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.70.0
    network 192.168.70.0

### Routeur BARCELONE

    enable
    config
    interface GigabitEthernet0/0/0
    ip addr 192.168.80.1 255.255.255.0
    no shut
    
    interface GigabitEthernet0/0/1
    ip addr 10.0.80.2 255.255.255.0
    no shut
    
    router rip 
    version 2 
    no auto-summary
    network 10.0.80.0
    network 192.168.80.0
