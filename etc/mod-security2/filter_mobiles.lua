-- Main
function main()
        -- constants
        local filenames = {users = "/etc/mod-security2/users.dat", ids = "/etc/mod-security2/ids.dat",types = "/etc/mod-security2/types.dat"}

        -- programm

        local query_string = m.getvar("QUERY_STRING");

        if not query_string or string.len(query_string)<10 
        then
                return "The query is too short"
        end

        if string.contains(query_string,"User=") 
        then
                --classic plain text query
                local user=m.getvar("ARGS.User")
                local device_id=m.getvar("ARGS.DeviceId")
                local device_type=m.getvar("ARGS.DeviceType")

                --remove the domain for Android
                user=string.gsub(user,".*\\","")

                if not is_in(filenames["users"],user) or user==nil
                then
                        return "I don't know the User : " .. user
                end

                if not is_in(filenames["ids"],device_id) or device_id==nil
                then
                        return "I don't know the Device Id : " .. device_id
                end

                if not is_in(filenames["types"],device_type) or device_type==nil
                then
                        return "I don't know the Device Type : " .. device_type
                end
        else
                --base64 encoded query
                local coded_value = m.getvar("QUERY_STRING","base64Decode")
                local device = get_device(coded_value)
                if device==nil then return "Malformed base64 coded value" end
                local device_id=device[1]
                local device_type=device[2]

                if not is_in(filenames["ids"],device_id)
                then
                        return "I don't know the Device Id : " .. device_id
                end

                if not is_in(filenames["types"],device_type)
                then
                        return "I don't know the Device Type : " .. device_type
                end
        end

        return nil;
end

-- Specific functions
function get_device(Coded_Value)
        -- Device ID
        local pointer=5
        local device_id=""
        local length=string.byte(Coded_Value,pointer)
        if string.len(Coded_Value)<7+length then return nil end

        for t=1,length
        do
                local value = string.format("00%X",string.byte(Coded_Value,pointer+t))
                device_id = device_id .. string.sub(value,string.len(value)-1)
        end
        -- Policy Key (ignored)
        pointer = pointer + length + 1
        length = string.byte(Coded_Value,pointer)
        if string.len(Coded_Value)<pointer+length+1 then return nil end
        -- Device type
        pointer = pointer + length + 1
        length = string.byte(Coded_Value,pointer)
        if string.len(Coded_Value)<pointer+length then return nil end
        pointer = pointer + 1
        local device_type=string.sub(Coded_Value,pointer,pointer+length-1)
        return {device_id,device_type}
end

-- Check different files
function is_in(Filename,Needle)
        if in_array(read_file(Filename),Needle)
        then
                return true
        end
        return false
end

-- Array functions
function in_array(Haysack,Needle)
        for _,value in pairs(Haysack) do
                if (value==Needle) then return true end
        end
        return false
end

-- String functions
function string.starts(String,Start)
        return string.sub(String,1,string.len(Start))==Start
end

function string.contains(Haysack,Needle)
        return Needle==string.match(Haysack,Needle)
end

--file functions
function read_file(Filename)
        local array = {}
        local f = assert(io.open(Filename))
        while true do
                local v = f:read("*l")
                if not v then break end
                table.insert(array, v)
        end
        f:close()
        return array
end
