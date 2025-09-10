-- Initialize PostgreSQL for CrowPOS Multi-Tenant
-- This script runs when the PostgreSQL container is first created

-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Create landlord database if it doesn't exist
-- (This is handled by POSTGRES_DB environment variable)

-- Set timezone
SET timezone = 'UTC';

-- Grant CREATEDB privilege to the application user
-- This is required for creating tenant databases
ALTER USER crowpos CREATEDB;

-- Create a function to generate tenant database names
CREATE OR REPLACE FUNCTION generate_tenant_db_name(tenant_id UUID)
RETURNS TEXT AS $$
BEGIN
    RETURN 'tenant_' || replace(tenant_id::text, '-', '_');
END;
$$ LANGUAGE plpgsql;

-- Create a function to create tenant database
CREATE OR REPLACE FUNCTION create_tenant_database(tenant_id UUID)
RETURNS VOID AS $$
DECLARE
    db_name TEXT;
BEGIN
    db_name := generate_tenant_db_name(tenant_id);
    
    -- Create database
    EXECUTE 'CREATE DATABASE ' || quote_ident(db_name);
    
    -- Grant permissions
    EXECUTE 'GRANT ALL PRIVILEGES ON DATABASE ' || quote_ident(db_name) || ' TO crowpos';
END;
$$ LANGUAGE plpgsql;

-- Create a function to drop tenant database
CREATE OR REPLACE FUNCTION drop_tenant_database(tenant_id UUID)
RETURNS VOID AS $$
DECLARE
    db_name TEXT;
BEGIN
    db_name := generate_tenant_db_name(tenant_id);
    
    -- Terminate connections to the database
    EXECUTE 'SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = ' || quote_literal(db_name);
    
    -- Drop database
    EXECUTE 'DROP DATABASE IF EXISTS ' || quote_ident(db_name);
END;
$$ LANGUAGE plpgsql;
